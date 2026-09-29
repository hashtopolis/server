<?php

namespace Hashtopolis\inc\downloadapi;

use Hashtopolis\inc\apiv2\error\HttpForbidden;
use Hashtopolis\inc\apiv2\util\CorsHackMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

/**
 * CORS handling for the download endpoint, so the web-ui can download with its
 * apiv2 JWT when it is served from another origin than the backend. The origin
 * check is the same as for the apiv2 ({@see CorsHackMiddleware::CheckCORS()}).
 *
 * Preflight requests are answered here without authentication (browsers send
 * them without the Authorization header), all other responses including errors
 * get the CORS headers added. Requests without an Origin header (agents) are
 * not affected.
 *
 * Must be the outermost middleware, so it runs before routing and
 * authentication.
 */
final class DownloadCorsMiddleware implements MiddlewareInterface {
  public function process(Request $request, RequestHandler $handler): ResponseInterface {
    if ($request->getMethod() === 'OPTIONS') {
      $response = new Response(204);
    }
    else {
      $response = $handler->handle($request);
    }

    try {
      $response = CorsHackMiddleware::CheckCORS($request, $response);
    }
    catch (HttpForbidden $e) {
      $response = new Response(403);
      $response->getBody()->write($e->getMessage());
      return $response->withHeader('Content-Type', 'text/plain');
    }

    return $response
      ->withHeader('Access-Control-Allow-Methods', 'GET,OPTIONS')
      ->withHeader('Access-Control-Allow-Headers', $request->getHeaderLine('Access-Control-Request-Headers'))
      ->withHeader('Access-Control-Expose-Headers', 'Content-Disposition,Content-Length,Content-Range,ETag');
  }
}
