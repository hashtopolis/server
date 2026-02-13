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

use Hashtopolis\inc\apiv2\auth\HashtopolisAuthenticator;
use Hashtopolis\inc\apiv2\auth\JWTBeforeHandler;
use Hashtopolis\inc\apiv2\common\ClassMapper;
use Hashtopolis\inc\apiv2\common\error\ErrorHandler;
use Hashtopolis\inc\apiv2\common\util\CorsHackMiddleware;
use Hashtopolis\inc\apiv2\common\util\JsonBodyParserMiddleware;
use Hashtopolis\inc\apiv2\common\util\TokenAsParameterMiddleware;
use Hashtopolis\inc\apiv2\helper\AbortChunkHelperAPI;
use Hashtopolis\inc\apiv2\helper\AssignAgentHelperAPI;
use Hashtopolis\inc\apiv2\helper\BulkSupertaskBuilderHelperAPI;
use Hashtopolis\inc\apiv2\helper\ChangeOwnPasswordHelperAPI;
use Hashtopolis\inc\apiv2\helper\CreateSuperHashlistHelperAPI;
use Hashtopolis\inc\apiv2\helper\CreateSupertaskHelperAPI;
use Hashtopolis\inc\apiv2\helper\CurrentUserHelperAPI;
use Hashtopolis\inc\apiv2\helper\ExportCrackedHashesHelperAPI;
use Hashtopolis\inc\apiv2\helper\ExportLeftHashesHelperAPI;
use Hashtopolis\inc\apiv2\helper\ExportWordlistHelperAPI;
use Hashtopolis\inc\apiv2\helper\GetAccessGroupsHelperAPI;
use Hashtopolis\inc\apiv2\helper\GetAgentBinaryHelperAPI;
use Hashtopolis\inc\apiv2\helper\GetCracksOfTaskHelper;
use Hashtopolis\inc\apiv2\helper\GetFileHelperAPI;
use Hashtopolis\inc\apiv2\helper\GetTaskProgressImageHelperAPI;
use Hashtopolis\inc\apiv2\helper\GetUserPermissionHelperAPI;
use Hashtopolis\inc\apiv2\helper\ImportCrackedHashesHelperAPI;
use Hashtopolis\inc\apiv2\helper\ImportFileHelperAPI;
use Hashtopolis\inc\apiv2\helper\MaskSupertaskBuilderHelperAPI;
use Hashtopolis\inc\apiv2\helper\PurgeTaskHelperAPI;
use Hashtopolis\inc\apiv2\helper\RebuildChunkCacheHelperAPI;
use Hashtopolis\inc\apiv2\helper\RecountFileLinesHelperAPI;
use Hashtopolis\inc\apiv2\helper\RescanGlobalFilesHelperAPI;
use Hashtopolis\inc\apiv2\helper\ResetChunkHelperAPI;
use Hashtopolis\inc\apiv2\helper\ResetUserPasswordHelperAPI;
use Hashtopolis\inc\apiv2\helper\SearchHashesHelperAPI;
use Hashtopolis\inc\apiv2\helper\SetUserPasswordHelperAPI;
use Hashtopolis\inc\apiv2\helper\TaskExtraDetailsHelper;
use Hashtopolis\inc\apiv2\helper\UnassignAgentHelperAPI;
use Hashtopolis\inc\apiv2\model\AccessGroupAPI;
use Hashtopolis\inc\apiv2\model\AgentAPI;
use Hashtopolis\inc\apiv2\model\AgentAssignmentAPI;
use Hashtopolis\inc\apiv2\model\AgentBinaryAPI;
use Hashtopolis\inc\apiv2\model\AgentErrorAPI;
use Hashtopolis\inc\apiv2\model\AgentStatAPI;
use Hashtopolis\inc\apiv2\model\ChunkAPI;
use Hashtopolis\inc\apiv2\model\ConfigAPI;
use Hashtopolis\inc\apiv2\model\ConfigSectionAPI;
use Hashtopolis\inc\apiv2\model\CrackerBinaryAPI;
use Hashtopolis\inc\apiv2\model\CrackerBinaryTypeAPI;
use Hashtopolis\inc\apiv2\model\FileAPI;
use Hashtopolis\inc\apiv2\model\GlobalPermissionGroupAPI;
use Hashtopolis\inc\apiv2\model\HashAPI;
use Hashtopolis\inc\apiv2\model\HashlistAPI;
use Hashtopolis\inc\apiv2\model\HashTypeAPI;
use Hashtopolis\inc\apiv2\model\HealthCheckAgentAPI;
use Hashtopolis\inc\apiv2\model\HealthCheckAPI;
use Hashtopolis\inc\apiv2\model\LogEntryAPI;
use Hashtopolis\inc\apiv2\model\NotificationSettingAPI;
use Hashtopolis\inc\apiv2\model\PreprocessorAPI;
use Hashtopolis\inc\apiv2\model\PreTaskAPI;
use Hashtopolis\inc\apiv2\model\SpeedAPI;
use Hashtopolis\inc\apiv2\model\SupertaskAPI;
use Hashtopolis\inc\apiv2\model\TaskAPI;
use Hashtopolis\inc\apiv2\model\TaskWrapperAPI;
use Hashtopolis\inc\apiv2\model\UserAPI;
use Hashtopolis\inc\apiv2\model\VoucherAPI;

use DI\Container;
use Hashtopolis\inc\StartupConfig;
use Psr\Container\ContainerInterface;
use Slim\Factory\AppFactory;
use Slim\Middleware\ContentLengthMiddleware;
use Slim\Exception\HttpMethodNotAllowedException;

use Tuupola\Middleware\HttpBasicAuthentication;

use JimTools\JwtAuth\Decoder\FirebaseDecoder;
use JimTools\JwtAuth\Middleware\JwtAuthentication;
use JimTools\JwtAuth\Options;
use JimTools\JwtAuth\Secret;
use JimTools\JwtAuth\Exceptions\AuthorizationException;

use Middlewares\DeflateEncoder;

use Psr\Http\Message\ServerRequestInterface as Request;

use JimTools\JwtAuth\Rules\RequestMethodRule;
use JimTools\JwtAuth\Rules\RequestPathRule;

require_once(__DIR__ . "/../../../vendor/autoload.php");
require_once(__DIR__ . "/../../inc/startup/include.php");

/* Construct container for middleware */
$container = new Container();
AppFactory::setContainer($container);

$container->set("HttpBasicAuthentication", function (ContainerInterface $container) {
  return new HttpBasicAuthentication([
    "path" => "/api/v2/auth/token",
    "secure" => false,
    "error" => function ($response, $arguments) {
      return ErrorHandler::errorResponse($response, $arguments["message"], 401);
    },
    "authenticator" => new HashtopolisAuthenticator,
    "before" => function ($request, $arguments) {
      return $request->withAttribute("user", $arguments["user"]);
    }
  ]);
});

$container->set("classMapper", function () {
  return new ClassMapper();
});

/* API token validation */
$container->set("JwtAuthentication", function (ContainerInterface $container) {
  $decoder = new FirebaseDecoder(
    new Secret(StartupConfig::getInstance()->getPepper(0), 'HS256', hash("sha256", StartupConfig::getInstance()->getPepper(0)))
  );

  $options = new Options(
    isSecure: false,
    attribute: null,
    before: new JWTBeforeHandler
  );

  $rules = [
    new RequestPathRule(ignore: ["/api/v2/auth/token", "/api/v2/auth/oauth-token", "/api/v2/helper/resetUserPassword", "/api/v2/openapi.json"]),
    new RequestMethodRule(ignore: ["OPTIONS"])
  ];
  return new JwtAuthentication($options, $decoder, $rules);
});

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
  
  //Quirk to handle HTExceptions without status code, this can be removed when all HTExceptions have been migrated
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
  
  return ErrorHandler::errorResponse($response, $msg, $code);
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
  return ErrorHandler::errorResponse($response, $exception->getMessage(), 405);
});

include(__DIR__ . "/../../inc/apiv2/common/openAPISchema.routes.php");
include(__DIR__ . "/../../inc/apiv2/auth/token.routes.php");

// register model APIs
AccessGroupAPI::register($app);
AgentAPI::register($app);
AgentAssignmentAPI::register($app);
AgentBinaryAPI::register($app);
AgentErrorAPI::register($app);
AgentStatAPI::register($app);
ChunkAPI::register($app);
ConfigAPI::register($app);
ConfigSectionAPI::register($app);
CrackerBinaryAPI::register($app);
CrackerBinaryTypeAPI::register($app);
FileAPI::register($app);
GlobalPermissionGroupAPI::register($app);
HashAPI::register($app);
HashlistAPI::register($app);
HashTypeAPI::register($app);
HealthCheckAgentAPI::register($app);
HealthCheckAPI::register($app);
LogEntryAPI::register($app);
NotificationSettingAPI::register($app);
PreprocessorAPI::register($app);
PreTaskAPI::register($app);
SpeedAPI::register($app);
SupertaskAPI::register($app);
TaskAPI::register($app);
TaskWrapperAPI::register($app);
UserAPI::register($app);
VoucherAPI::register($app);

// register helpers
AbortChunkHelperAPI::register($app);
AssignAgentHelperAPI::register($app);
BulkSupertaskBuilderHelperAPI::register($app);
ChangeOwnPasswordHelperAPI::register($app);
CreateSuperHashlistHelperAPI::register($app);
CreateSupertaskHelperAPI::register($app);
CurrentUserHelperAPI::register($app);
ExportCrackedHashesHelperAPI::register($app);
ExportLeftHashesHelperAPI::register($app);
ExportWordlistHelperAPI::register($app);
GetAccessGroupsHelperAPI::register($app);
GetAgentBinaryHelperAPI::register($app);
GetCracksOfTaskHelper::register($app);
GetFileHelperAPI::register($app);
GetTaskProgressImageHelperAPI::register($app);
GetUserPermissionHelperAPI::register($app);
ImportCrackedHashesHelperAPI::register($app);
ImportFileHelperAPI::register($app);
MaskSupertaskBuilderHelperAPI::register($app);
PurgeTaskHelperAPI::register($app);
RebuildChunkCacheHelperAPI::register($app);
RecountFileLinesHelperAPI::register($app);
RescanGlobalFilesHelperAPI::register($app);
ResetChunkHelperAPI::register($app);
ResetUserPasswordHelperAPI::register($app);
SearchHashesHelperAPI::register($app);
SetUserPasswordHelperAPI::register($app);
TaskExtraDetailsHelper::register($app);
UnassignAgentHelperAPI::register($app);

$app->run();
