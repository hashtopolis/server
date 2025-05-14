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


use Slim\Psr7\Response;

use Skeleton\Domain\Token;
use Crell\ApiProblem\ApiProblem;

use Tuupola\Middleware\JwtAuthentication;
use Tuupola\Middleware\HttpBasicAuthentication;
use Tuupola\Middleware\HttpBasicAuthentication\AuthenticatorInterface;
use Tuupola\Middleware\CorsMiddleware;

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

require __DIR__ . "/../../../vendor/autoload.php";

require_once(dirname(__FILE__) . "/../../inc/load.php");

 
/* Construct container for middleware */
$container = new \DI\Container();
AppFactory::setContainer($container);


/* Quirk to display error JSON style */
function errorResponse($response, $message, $status = 401)
{
    $problem = new ApiProblem($message, "about:blank");
    $problem->setStatus($status);

    $body = $response->getBody();
    $body->write($problem->asJson(true));

    return $response
        ->withHeader("Content-type", "application/problem+json")
        ->withStatus($status);
}


/* Authentication middleware for token retrival */
class HashtopolisAuthenticator implements AuthenticatorInterface {
    public function __invoke(array $arguments): bool {
        $username = $arguments["user"];
        $password = $arguments["password"];

        $filter = new QueryFilter(User::USERNAME, $username, "=");
        
        $check = Factory::getUserFactory()->filter([Factory::FILTER => $filter]);
        if ($check === null || sizeof($check) == 0) {
            return false;
        }
        $user = $check[0];
        
        if ($user->getIsValid() != 1) {
            return false;
        }
        else if (!Encryption::passwordVerify($password, $user->getPasswordSalt(), $user->getPasswordHash())) {
            Util::createLogEntry(DLogEntryIssuer::USER, $user->getId(), DLogEntry::WARN, "Failed login attempt due to wrong password!");
            return false;
        }
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
  private $store = array();  
  public function add($key, $value) : void {
    $this->store[$key] = $value;
  }
  public function get($key): string {
    return $this->store[$key];
  }
}

$container->set("classMapper", function() {
  return new ClassMapper();
});

/* API token validation */
$container->set("JwtAuthentication", function (\Psr\Container\ContainerInterface $container) {
    include(dirname(__FILE__) . '/../../inc/confv2.php');
    return new JwtAuthentication([
        "path" => "/",
        "ignore" => ["/api/v2/auth/token", "/api/v2/helper/resetUserPassword", "/api/v2/openapi.json"],
        "secret" => $PEPPER[0],
        "attribute" => false,
        "secure" => false,
        "error" => function ($response, $arguments) {
            return errorResponse($response, $arguments["message"], 401);
        },
        "before" => function ($request, $arguments) use ($container) {
            // TODO: Validate if user is still allowed to login
            return $request->withAttribute("userId", $arguments["decoded"]["userId"])->withAttribute("scope", $arguments["decoded"]["scope"]);
        },
    ]);
});


/* Pre-parse incoming request body */
class JsonBodyParserMiddleware implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        $contentType = $request->getHeaderLine('Content-Type');

        if (strstr($contentType, 'application/json') || strstr($contentType, 'application/vnd.api+json')) {
            $contents = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $request = $request->withParsedBody($contents);
            } else {
                $response = new Response();
                return errorResponse($response, "Malformed request", 400);
            }
        }

        $response = $handler->handle($request);
        return $response;
    }
}

/* Quirk to map token as parameter (usefull for debugging) to 'Authorization Header (for JWT input) */
class TokenAsParameterMiddleware implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        $data = $request->getQueryParams();
        if (array_key_exists('token', $data)) {
            $request = $request->withHeader('Authorization', 'Bearer ' . $data['token']);
        };

        $response = $handler->handle($request);
        return $response;
    }
}


/* FIXME: CORS wildcard hack should require proper implementation and validation */
/* This middleware will append the response header Access-Control-Allow-Methods with all allowed methods */
class CorsHackMiddleware implements MiddlewareInterface 
{
    public function process(Request $request, RequestHandler $handler): Response {
        $routeContext = RouteContext::fromRequest($request);
        $routingResults = $routeContext->getRoutingResults();
        $methods = $routingResults->getAllowedMethods();
        $requestHeaders = $request->getHeaderLine('Access-Control-Request-Headers');
    
        $response = $handler->handle($request);
    
        $response = $response->withHeader('Access-Control-Allow-Origin', '*');
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
));

$app->add(new CorsHackMiddleware());            // NOTE: The RoutingMiddleware should be added after our CORS middleware so routing is performed first
$app->addRoutingMiddleware();

require __DIR__ . "/../../inc/apiv2/auth/token.routes.php";

require __DIR__ . "/../../inc/apiv2/common/openAPISchema.routes.php";

require __DIR__ . "/../../inc/apiv2/model/accessgroups.routes.php";
require __DIR__ . "/../../inc/apiv2/model/agentassignments.routes.php";
require __DIR__ . "/../../inc/apiv2/model/agentbinaries.routes.php";
require __DIR__ . "/../../inc/apiv2/model/agenterrors.routes.php";
require __DIR__ . "/../../inc/apiv2/model/agents.routes.php";
require __DIR__ . "/../../inc/apiv2/model/agentstats.routes.php";
require __DIR__ . "/../../inc/apiv2/model/chunks.routes.php";
require __DIR__ . "/../../inc/apiv2/model/configs.routes.php";
require __DIR__ . "/../../inc/apiv2/model/configsections.routes.php";
require __DIR__ . "/../../inc/apiv2/model/crackers.routes.php";
require __DIR__ . "/../../inc/apiv2/model/crackertypes.routes.php";
require __DIR__ . "/../../inc/apiv2/model/files.routes.php";
require __DIR__ . "/../../inc/apiv2/model/globalpermissiongroups.routes.php";
require __DIR__ . "/../../inc/apiv2/model/hashes.routes.php";
require __DIR__ . "/../../inc/apiv2/model/hashlists.routes.php";
require __DIR__ . "/../../inc/apiv2/model/hashtypes.routes.php";
require __DIR__ . "/../../inc/apiv2/model/healthcheckagents.routes.php";
require __DIR__ . "/../../inc/apiv2/model/healthchecks.routes.php";
require __DIR__ . "/../../inc/apiv2/model/logentries.routes.php";
require __DIR__ . "/../../inc/apiv2/model/notifications.routes.php";
require __DIR__ . "/../../inc/apiv2/model/preprocessors.routes.php";
require __DIR__ . "/../../inc/apiv2/model/pretasks.routes.php";
require __DIR__ . "/../../inc/apiv2/model/speeds.routes.php";
require __DIR__ . "/../../inc/apiv2/model/supertasks.routes.php";
require __DIR__ . "/../../inc/apiv2/model/tasks.routes.php";
require __DIR__ . "/../../inc/apiv2/model/taskwrappers.routes.php";
require __DIR__ . "/../../inc/apiv2/model/users.routes.php";
require __DIR__ . "/../../inc/apiv2/model/vouchers.routes.php";

require __DIR__ . "/../../inc/apiv2/helper/abortChunk.routes.php";
require __DIR__ . "/../../inc/apiv2/helper/assignAgent.routes.php";
require __DIR__ . "/../../inc/apiv2/helper/createSupertask.routes.php";
require __DIR__ . "/../../inc/apiv2/helper/createSuperHashlist.routes.php";
require __DIR__ . "/../../inc/apiv2/helper/exportCrackedHashes.routes.php";
require __DIR__ . "/../../inc/apiv2/helper/exportLeftHashes.routes.php";
require __DIR__ . "/../../inc/apiv2/helper/exportWordlist.routes.php";
require __DIR__ . "/../../inc/apiv2/helper/getFile.routes.php";
require __DIR__ . "/../../inc/apiv2/helper/importCrackedHashes.routes.php";
require __DIR__ . "/../../inc/apiv2/helper/importFile.routes.php";
require __DIR__ . "/../../inc/apiv2/helper/purgeTask.routes.php";
require __DIR__ . "/../../inc/apiv2/helper/recountFileLines.routes.php";
require __DIR__ . "/../../inc/apiv2/helper/resetChunk.routes.php";
require __DIR__ . "/../../inc/apiv2/helper/resetUserPassword.routes.php";
require __DIR__ . "/../../inc/apiv2/helper/setUserPassword.routes.php";
require __DIR__ . "/../../inc/apiv2/helper/unassignAgent.routes.php";

// NOTE: The ErrorMiddleware should be added after any middleware which may modify the response body
$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$errorHandler = $errorMiddleware->getDefaultErrorHandler();
$errorHandler->forceContentType('application/json');

$app->run();
