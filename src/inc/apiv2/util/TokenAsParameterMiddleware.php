<?php

namespace Hashtopolis\inc\apiv2\util;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

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