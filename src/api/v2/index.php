<?php
declare(strict_types=1);

date_default_timezone_set("UTC");
error_reporting(E_ALL);
ini_set("display_errors", '1');

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

        $arguments['foobar'] = 'True';
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
    include(dirname(__FILE__) . '/../../inc/conf.php');
    return new JwtAuthentication([
        "path" => "/",
        "ignore" => ["/api/v2/auth/token"],
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

        if (strstr($contentType, 'application/json')) {
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
$app->add(new CorsHackMiddleware());            // NOTE: The RoutingMiddleware should be added after our CORS middleware so routing is performed first
$app->addRoutingMiddleware();

require __DIR__ . "/../../inc/apiv2/schema.routes.php";
require __DIR__ . "/../../inc/apiv2/accessgroups.routes.php";
require __DIR__ . "/../../inc/apiv2/globalpermissiongroups.routes.php";
require __DIR__ . "/../../inc/apiv2/agents.routes.php";
require __DIR__ . "/../../inc/apiv2/agentstats.routes.php";
require __DIR__ . "/../../inc/apiv2/agentbinaries.routes.php";
require __DIR__ . "/../../inc/apiv2/chunks.routes.php";
require __DIR__ . "/../../inc/apiv2/configs.routes.php";
require __DIR__ . "/../../inc/apiv2/configsections.routes.php";
require __DIR__ . "/../../inc/apiv2/crackers.routes.php";
require __DIR__ . "/../../inc/apiv2/crackertypes.routes.php";
require __DIR__ . "/../../inc/apiv2/files.import.routes.php";
require __DIR__ . "/../../inc/apiv2/files.routes.php";
require __DIR__ . "/../../inc/apiv2/hashes.routes.php";
require __DIR__ . "/../../inc/apiv2/hashlists.routes.php";
require __DIR__ . "/../../inc/apiv2/hashtypes.routes.php";
require __DIR__ . "/../../inc/apiv2/healthchecks.routes.php";
require __DIR__ . "/../../inc/apiv2/healthcheckagents.routes.php";
require __DIR__ . "/../../inc/apiv2/logentries.routes.php";
require __DIR__ . "/../../inc/apiv2/notifications.routes.php";
require __DIR__ . "/../../inc/apiv2/pretasks.routes.php";
require __DIR__ . "/../../inc/apiv2/preprocessors.routes.php";
require __DIR__ . "/../../inc/apiv2/supertasks.routes.php";
require __DIR__ . "/../../inc/apiv2/tasks.routes.php";
require __DIR__ . "/../../inc/apiv2/token.routes.php";
require __DIR__ . "/../../inc/apiv2/users.routes.php";
require __DIR__ . "/../../inc/apiv2/vouchers.routes.php";
require __DIR__ . "/../../inc/apiv2/taskwrappers.routes.php";


$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$errorHandler = $errorMiddleware->getDefaultErrorHandler();
$errorHandler->forceContentType('application/json');

$app->run();
