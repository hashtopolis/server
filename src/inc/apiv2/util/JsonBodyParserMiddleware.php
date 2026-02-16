<?php

namespace Hashtopolis\inc\apiv2\util;

use Hashtopolis\inc\apiv2\common\error\ErrorHandler;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Psr7\Response;


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
        return ErrorHandler::errorResponse($response, "Malformed request", 400);
      }
    }
    
    return $handler->handle($request);
  }
}