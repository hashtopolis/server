<?php
declare(strict_types=1);

$enabled = getenv('HASHTOPOLIS_APIV2_ENABLE');
if (!$enabled || $enabled == 'false') {
  die("APIv2 is not enabled, it can be enabled via environment variable!");
}

date_default_timezone_set("UTC");
error_reporting(E_ALL ^ E_DEPRECATED);
ini_set("display_errors", '1');
/**
 * Treat warnings as error, very usefull during unit testing.
 * TODO: How-ever during Xdebug debugging under VS Code, this is very
 * TODO: slightly annoying since the last call stack is not very interesting.
 * TODO: Thus for the time-being do not-enable by default.
 */
// set_error_handler(function ($severity, $message, $file, $line) {
//   if (error_reporting() & $severity) {
//       throw new \ErrorException($message, 0, $severity, $file, $line);
//   }
// });

use Slim\Factory\AppFactory;
use Slim\Middleware\ContentLengthMiddleware;
use Slim\Routing\RouteContext;
use Slim\Exception\HttpMethodNotAllowedException;

use Slim\Psr7\Response;

use Skeleton\Domain\Token;

use Tuupola\Middleware\HttpBasicAuthentication;
use Tuupola\Middleware\HttpBasicAuthentication\AuthenticatorInterface;
use Tuupola\Middleware\CorsMiddleware;

use JimTools\JwtAuth\Decoder\FirebaseDecoder;
use JimTools\JwtAuth\Middleware\JwtAuthentication;
use JimTools\JwtAuth\Options;
use JimTools\JwtAuth\Secret;
use JimTools\JwtAuth\Exceptions\AuthorizationException;

use Middlewares\DeflateEncoder;

use Skeleton\Application\Response\UnauthorizedResponse;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

use DBA\QueryFilter;
use DBA\Session;
use DBA\User;
use DBA\Factory;
use JimTools\JwtAuth\Handlers\BeforeHandlerInterface;
use JimTools\JwtAuth\Rules\RequestMethodRule;
use JimTools\JwtAuth\Rules\RequestPathRule;
use Psr\Http\Message\ServerRequestInterface;

require_once(__DIR__ . "/../../../vendor/autoload.php");
require_once(__DIR__ . "/../../inc/apiv2/common/ErrorHandler.class.php");

require_once(dirname(__FILE__) . "/../../inc/startup/include.php");


/* Construct container for middleware */
$container = new \DI\Container();
AppFactory::setContainer($container);


class JWTBeforeHandler implements BeforeHandlerInterface {
  /**
   * @param array{decoded: array<string, mixed>, token: string} $arguments
   */
  public function __invoke(ServerRequestInterface $request, array $arguments): ServerRequestInterface
  {
    // adds the decoded userId and scope to the request attributes
    return $request->withAttribute("userId", $arguments["decoded"]["userId"])->withAttribute("scope", $arguments["decoded"]["scope"]);
  }
}

/* Authentication middleware for token retrival */

class HashtopolisAuthenticator implements AuthenticatorInterface {
  public function __invoke(array $arguments): bool {
    $username = $arguments["user"];
    $password = $arguments["password"];
    
    $filter = new QueryFilter(User::USERNAME, $username, "=");
    
    $user = Factory::getUserFactory()->filter([Factory::FILTER => $filter], true);
    if ($user === null) {
      return false;
    }
    
    if ($user->getIsValid() != 1) {
      return false;
    }
    else if (!Encryption::passwordVerify($password, $user->getPasswordSalt(), $user->getPasswordHash())) {
      Util::createLogEntry(DLogEntryIssuer::USER, $user->getId(), DLogEntry::WARN, "Failed login attempt due to wrong password!");
      return false;
    }
    Factory::getUserFactory()->set($user, User::LAST_LOGIN_DATE, time());
    return true;
  }
}

$container->set("HttpBasicAuthentication", function (\Psr\Container\ContainerInterface $container) {
  return new HttpBasicAuthentication([
    "path" => "/api/v2/auth/token",
    "secure" => false,
    "error" => function ($response, $arguments) {
      return errorResponse($response, $arguments["message"], 401);
    },
    "authenticator" => new HashtopolisAuthenticator,
    "before" => function ($request, $arguments) {
      return $request->withAttribute("user", $arguments["user"]);
    }
  ]);
});

/* Quick to create auto-generated lookup table between DBA Objects and APIv2 classes */

class ClassMapper {
  private array $store = array();
  
  public function add($key, $value): void {
    $this->store[$key] = $value;
  }
  
  public function get($key): string {
    return $this->store[$key];
  }
}

$container->set("classMapper", function () {
  return new ClassMapper();
});

/* API token validation */
$container->set("JwtAuthentication", function (\Psr\Container\ContainerInterface $container) {
  $decoder = new FirebaseDecoder(
    new Secret(StartupConfig::getInstance()->getPepper(0), 'HS256', hash("sha256", StartupConfig::getInstance()->getPepper(0)))
  );

  $options = new Options(
    isSecure: false,
    before: new JWTBeforeHandler,
    attribute: null
  );

  $rules = [
    new RequestPathRule(ignore: ["/api/v2/auth/token", "/api/v2/auth/oauth-token", "/api/v2/helper/resetUserPassword", "/api/v2/openapi.json"]),
    new RequestMethodRule(ignore: ["OPTIONS"])
  ];
  return new JwtAuthentication($options, $decoder, $rules);
});
  
/* Pre-parse incoming request body */

class JsonBodyParserMiddleware implements MiddlewareInterface {
  public function process(Request $request, RequestHandler $handler): Response {
    $contentType = $request->getHeaderLine('Content-Type');
    
    if (strstr($contentType, 'application/json') || strstr($contentType, 'application/vnd.api+json')) {
      $contents = json_decode(file_get_contents('php://input'), true);
      if (json_last_error() === JSON_ERROR_NONE) {
        $request = $request->withParsedBody($contents);
      }
      else {
        $response = new Response();
        return errorResponse($response, "Malformed request", 400);
      }
    }
    
    return $handler->handle($request);
  }
}

/* Quirk to map token as parameter (useful for debugging) to 'Authorization Header (for JWT input) */

class TokenAsParameterMiddleware implements MiddlewareInterface {
  public function process(Request $request, RequestHandler $handler): Response {
    $data = $request->getQueryParams();
    if (array_key_exists('token', $data)) {
      $request = $request->withHeader('Authorization', 'Bearer ' . $data['token']);
    };
    
    return $handler->handle($request);
  }
}


/* This middleware will append the response header Access-Control-Allow-Methods with all allowed methods */

class CorsHackMiddleware implements MiddlewareInterface {
  public function process(Request $request, RequestHandler $handler): Response {
    $response = $handler->handle($request);
    
    return $this::addCORSheaders($request, $response);
  }
  
  public static function addCORSheaders(Request $request, $response) {
    $routeContext = RouteContext::fromRequest($request);
    $routingResults = $routeContext->getRoutingResults();
    $methods = $routingResults->getAllowedMethods();
    
    $requestHeaders = $request->getHeaderLine('Access-Control-Request-Headers');
    $requestHttpOrigin = $request->getHeaderLine('HTTP_ORIGIN');

    $envBackend = getenv('HASHTOPOLIS_BACKEND_URL');
    
    if ($envBackend !== false) {
      $requestHttpOrigin = explode('://', $requestHttpOrigin)[1];
      $envBackend = explode('://', $envBackend)[1];

      $envBackend = explode('/', $envBackend)[0];

      $requestHttpOriginUrl = substr($requestHttpOrigin, 0, strrpos($requestHttpOrigin, ":")); //Needs to use strrpos in case of ipv6 because of multiple ':' characters
      $envBackendUrl = substr($envBackend, 0, strrpos($envBackend, ":"));
      
      $localhostSynonyms = ["localhost", "127.0.0.1", "[::1]"];
      
      if ($requestHttpOriginUrl === $envBackendUrl || (in_array($requestHttpOriginUrl, $localhostSynonyms) && in_array($envBackendUrl, $localhostSynonyms))) {
        //Origin URL matches, now check the port too
        $requestHttpOriginPort = substr($envBackend, strrpos($envBackend, ":")); //Needs to use strrpos in case of ipv6 because of multiple ':' characters
        $envBackendPort = substr($envBackend, strrpos($envBackend, ":"));

        if ($requestHttpOriginPort === $envBackendPort || $requestHttpOriginPort === "4200") {
          $response = $response->withHeader('Access-Control-Allow-Origin', $request->getHeaderLine('HTTP_ORIGIN'));
        }
        else {
          error_log("CORS error: Allow-Origin port doesn't match. Try switching the frontend port back to the default value (4200) in the docker-compose.");
          die();
        }
      }
      else {
        error_log("CORS error: Allow-Origin URL doesn't match. Is the HASHTOPOLIS_BACKEND_URL in the .env file the correct one?");
        die();
      }
    }
    else {
      //No backend URL given in .env file, switch to default allow all
      $response = $response->withHeader('Access-Control-Allow-Origin', '*');
    }
    
    $response = $response->withHeader('Access-Control-Allow-Methods', implode(',', $methods));
    $response = $response->withHeader('Access-Control-Allow-Headers', $requestHeaders);
    
    // Optional: Allow Ajax CORS requests with Authorization header
    // $response = $response->withHeader('Access-Control-Allow-Credentials', 'true');
    return $response;
  }
}

/* 
 * SLIM framework middleware requires specific order to ensure middleware layers are executed in correct order.
 * Also see https://www.slimframework.com/docs/v4/concepts/middleware.html for details. 
 * 
 * When you run the Slim application, the Request object traverses the middleware structure from the outside in. 
 * They first enter the outer-most middleware, then the next outer-most middleware, (and so on), until they ultimately 
 * arrive at the Slim application itself. After the Slim application dispatches the appropriate route, the resultant Response 
 * object exits the Slim application and traverses the middleware structure from the inside out. Ultimately, a final Response 
 * object exits the outer-most middleware, is serialized into a raw HTTP response, and is returned to the HTTP client. 
 */
$app = AppFactory::create();

$app->addBodyParsingMiddleware();
$app->add(new JsonBodyParserMiddleware());
$app->add("HttpBasicAuthentication");
$app->add("JwtAuthentication");
$app->add(new TokenAsParameterMiddleware());
$app->add(new ContentLengthMiddleware());       // NOTE: Add any middleware which may modify the response body before adding the ContentLengthMiddleware
$app->add((new DeflateEncoder())->contentType(
  '/^(image\/svg\\+xml|text\/.*|application\/json|"application\/vnd\.api+json)(;.*)?$/'
)
);

$app->add(new CorsHackMiddleware());            // NOTE: The RoutingMiddleware should be added after our CORS middleware so routing is performed first
// NOTE: The ErrorMiddleware should be added after any middleware which may modify the response body
$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$errorHandler = $errorMiddleware->getDefaultErrorHandler();
$errorHandler->forceContentType('application/json');

$customErrorHandler = function (
  Request $request,
  Throwable $exception,
  bool $displayErrorDetails,
  bool $logErrors,
  bool $logErrorDetails) use ($app) {
  
  $response = $app->getResponseFactory()->createResponse();
  $response = CorsHackMiddleware::addCORSheaders($request, $response);
  
  //Quirck to handle HTexceptions without status code, this can be removed when all HTexceptions have been migrated
  error_log($exception->getMessage());
  $code = $exception->getCode();
  if ($code == 0 || $code == 1 || !is_integer($code)) {
    $code = 500;
  }

  $msg = $exception->getMessage();

  if ($exception instanceof AuthorizationException && empty($msg)) {
    //the JWT authorization exceptions are wrapped in an outer exception
    $previous = $exception->getPrevious();
    if ($previous !== null) {
      $code = 400;
      $msg = $previous->getMessage();
    }
  }

  
  return errorResponse($response, $msg, $code);
};
$errorMiddleware->setDefaultErrorHandler($customErrorHandler);
$app->addRoutingMiddleware(); //Routing middleware has to be added after the default error handler
$errorMiddlewareMethodNotAllowed = $app->addErrorMiddleware(true, true, true);
$errorMiddlewareMethodNotAllowed->setErrorHandler(HttpMethodNotAllowedException::class, function (
  Request $request,
  Throwable $exception,
  bool $displayErrorDetails,
  bool $logErrors,
  bool $logErrorDetails) use ($app) {
  $response = $app->getResponseFactory()->createResponse();
  return errorResponse($response, $exception->getMessage(), 405);
});


require_once(__DIR__ . "/../../inc/apiv2/auth/token.routes.php");
require_once(__DIR__ . "/../../inc/apiv2/common/openAPISchema.routes.php");

$modelDir = __DIR__ . "/../../inc/apiv2/model";

require_once($modelDir . "/accessgroups.routes.php");
require_once($modelDir . "/agentassignments.routes.php");
require_once($modelDir . "/agentbinaries.routes.php");
require_once($modelDir . "/agenterrors.routes.php");
require_once($modelDir . "/agents.routes.php");
require_once($modelDir . "/agentstats.routes.php");
require_once($modelDir . "/chunks.routes.php");
require_once($modelDir . "/configs.routes.php");
require_once($modelDir . "/configsections.routes.php");
require_once($modelDir . "/crackers.routes.php");
require_once($modelDir . "/crackertypes.routes.php");
require_once($modelDir . "/files.routes.php");
require_once($modelDir . "/globalpermissiongroups.routes.php");
require_once($modelDir . "/hashes.routes.php");
require_once($modelDir . "/hashlists.routes.php");
require_once($modelDir . "/hashtypes.routes.php");
require_once($modelDir . "/healthcheckagents.routes.php");
require_once($modelDir . "/healthchecks.routes.php");
require_once($modelDir . "/logentries.routes.php");
require_once($modelDir . "/notifications.routes.php");
require_once($modelDir . "/preprocessors.routes.php");
require_once($modelDir . "/pretasks.routes.php");
require_once($modelDir . "/speeds.routes.php");
require_once($modelDir . "/supertasks.routes.php");
require_once($modelDir . "/tasks.routes.php");
require_once($modelDir . "/taskwrappers.routes.php");
require_once($modelDir . "/users.routes.php");
require_once($modelDir . "/vouchers.routes.php");

$helperDir = __DIR__ . "/../../inc/apiv2/helper";

require_once($helperDir . "/abortChunk.routes.php");
require_once($helperDir . "/assignAgent.routes.php");
require_once($helperDir . "/bulkSupertaskBuilder.routes.php");
require_once($helperDir . "/changeOwnPassword.routes.php");
require_once($helperDir . "/currentUser.routes.php");
require_once($helperDir . "/createSupertask.routes.php");
require_once($helperDir . "/createSuperHashlist.routes.php");
require_once($helperDir . "/exportCrackedHashes.routes.php");
require_once($helperDir . "/exportLeftHashes.routes.php");
require_once($helperDir . "/exportWordlist.routes.php");
require_once($helperDir . "/getAccessGroups.routes.php");
require_once($helperDir . "/getAgentBinary.routes.php");
require_once($helperDir . "/getCracksOfTask.routes.php");
require_once($helperDir . "/getFile.routes.php");
require_once($helperDir . "/getTaskProgressImage.routes.php");
require_once($helperDir . "/getUserPermission.routes.php");
require_once($helperDir . "/importCrackedHashes.routes.php");
require_once($helperDir . "/importFile.routes.php");
require_once($helperDir . "/maskSupertaskBuilder.routes.php");
require_once($helperDir . "/purgeTask.routes.php");
require_once($helperDir . "/rebuildChunkCache.routes.php");
require_once($helperDir . "/recountFileLines.routes.php");
require_once($helperDir . "/rescanGlobalFiles.routes.php");
require_once($helperDir . "/resetChunk.routes.php");
require_once($helperDir . "/resetUserPassword.routes.php");
require_once($helperDir . "/searchHashes.routes.php");
require_once($helperDir . "/setUserPassword.routes.php");
require_once($helperDir . "/taskExtraDetails.routes.php");
require_once($helperDir . "/unassignAgent.routes.php");

$app->run();
