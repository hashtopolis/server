<?php

namespace Hashtopolis\inc\apiv2\common\util;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Psr7\Response;
use Slim\Routing\RouteContext;

/* This middleware will append the response header Access-Control-Allow-Methods with all allowed methods */
class CorsHackMiddleware implements MiddlewareInterface {
  public function process(Request $request, RequestHandler $handler): Response {
    $response = $handler->handle($request);
    
    return $this::addCORSHeaders($request, $response);
  }
  
  public static function addCORSHeaders(Request $request, $response) {
    $routeContext = RouteContext::fromRequest($request);
    $routingResults = $routeContext->getRoutingResults();
    $methods = $routingResults->getAllowedMethods();
    $requestHeaders = $request->getHeaderLine('Access-Control-Request-Headers');
    
    $frontend_urls = getenv('HASHTOPOLIS_FRONTEND_URLS');
    if ($frontend_urls !== false) {
      if (in_array($request->getHeaderLine('Origin'), explode(',', $frontend_urls), true)) {
        $response = $response->withHeader('Access-Control-Allow-Origin', $request->getHeaderLine('Origin'));
      }
      else {
        error_log("CORS error: Allow-Origin doesn't match. Please make sure to include the used frontend in the .env file.");
      }
    }
    else {
      //No frontend URLs given in .env file, switch to default allow all
      $response = $response->withHeader('Access-Control-Allow-Origin', '*');
    }
    
    $response = $response->withHeader('Access-Control-Allow-Methods', implode(',', $methods));
    $response = $response->withHeader('Access-Control-Allow-Headers', $requestHeaders);
    
    // Optional: Allow Ajax CORS requests with Authorization header
    // $response = $response->withHeader('Access-Control-Allow-Credentials', 'true');
    return $response;
  }
}