<?php

namespace Hashtopolis\inc\downloadapi;

use Exception;
use Hashtopolis\inc\Util;
use JimTools\JwtAuth\Exceptions\AuthorizationException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Exception\HttpNotFoundException;
use Slim\Factory\AppFactory;
use Slim\Psr7\Response;
use Throwable;

/**
 * Creates the Slim 4 application for the download API. The single GET route
 * serves downloads of resources like locally stored cracker binary archives,
 * the download kind in the url is dispatched via {@see DownloadRegistry}.
 * Authentication is done by {@see DownloadAuthMiddleware} with either an
 * agent token or an apiv2 JWT, CORS for the web-ui by {@see DownloadCorsMiddleware}.
 */
final class DownloadApp {
  public static function create(): App {
    $app = AppFactory::create();

    /* Servers reachable under a deployment subpath (HASHTOPOLIS_BACKEND_URL
     * like 'https://example.com/hashtopolis/api/v2') get download URLs which
     * contain that subpath, so the routes have to be served under the same
     * prefix. When no usable backend base url is configured the routes stay at
     * the root instead of failing the endpoint as a whole. */
    try {
      $basePath = parse_url(Util::buildBackendBaseUrl(), PHP_URL_PATH);
    }
    catch (Exception) {
      $basePath = false;
    }
    if (is_string($basePath) && $basePath !== '' && $basePath !== '/') {
      $app->getRouteCollector()->setBasePath($basePath);
    }

    $app->add(new DownloadAuthMiddleware());

    $errorMiddleware = $app->addErrorMiddleware(true, true, true);
    $errorMiddleware->setDefaultErrorHandler(
      function (
        Request $request,
        Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails,
      ): ResponseInterface {
        error_log("DownloadApi: " . $exception->getMessage());
        $response = new Response(500);
        $response->getBody()->write('Internal server error');
        return $response;
      }
    );
    $errorMiddleware->setErrorHandler(AuthorizationException::class, function (
      Request $request,
      Throwable $exception,
      bool $displayErrorDetails,
      bool $logErrors,
      bool $logErrorDetails,
    ): ResponseInterface {
      $response = new Response(401);
      $response->getBody()->write('No access!');
      return $response;
    });
    $errorMiddleware->setErrorHandler(HttpNotFoundException::class, function (
      Request $request,
      Throwable $exception,
      bool $displayErrorDetails,
      bool $logErrors,
      bool $logErrorDetails,
    ): ResponseInterface {
      $response = new Response(404);
      $response->getBody()->write('Not found');
      return $response;
    });
    $app->addRoutingMiddleware();
    // outermost, so preflight requests are answered before routing and authentication
    $app->add(new DownloadCorsMiddleware());

    $app->get('/api/download.php/{kind}/{id}', function (Request $request, Response $response, array $args): ResponseInterface {
      $handlerClass = DownloadRegistry::getHandler($args['kind']);
      if ($handlerClass === null) {
        $response->getBody()->write('Unknown download kind!');
        return $response->withStatus(404)->withHeader('Content-Type', 'text/plain');
      }
      /** @var CrackerBinaryDownloadHandler $handler */
      $handler = new $handlerClass();
      return $handler($request, $response, $args);
    });

    return $app;
  }
}
