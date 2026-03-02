<?php

namespace Hashtopolis\inc\apiv2\util;

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
    $requestHttpOrigin = $request->getHeaderLine('HTTP_ORIGIN');
    
    $envBackend = getenv('HASHTOPOLIS_BACKEND_URL');
    $envFrontendPort = getenv('HASHTOPOLIS_FRONTEND_PORT');
    
    if ($envBackend !== false || $envFrontendPort !== false) {
      $requestHttpOrigin = explode('://', $requestHttpOrigin)[1];
      $envBackend = explode('://', $envBackend)[1];
      
      $envBackend = explode('/', $envBackend)[0];
      
      $requestHttpOriginUrl = substr($requestHttpOrigin, 0, strrpos($requestHttpOrigin, ":")); //Needs to use strrpos in case of ipv6 because of multiple ':' characters
      $envBackendUrl = substr($envBackend, 0, strrpos($envBackend, ":"));
      
      $localhostSynonyms = ["localhost", "127.0.0.1", "[::1]"];
      
      if ($requestHttpOriginUrl === $envBackendUrl || (in_array($requestHttpOriginUrl, $localhostSynonyms) && in_array($envBackendUrl, $localhostSynonyms))) {
        //Origin URL matches, now check the port too
        $requestHttpOriginPort = substr($requestHttpOrigin, strrpos($requestHttpOrigin, ":") + 1); //Needs to use strrpos in case of ipv6 because of multiple ':' characters
        $envBackendPort = substr($envBackend, strrpos($envBackend, ":") + 1);
        
        if ($requestHttpOriginPort === $envFrontendPort || $requestHttpOriginPort === $envBackendPort) {
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