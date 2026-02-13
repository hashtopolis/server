<?php

namespace Hashtopolis\inc\apiv2\common\error;

use Crell\ApiProblem\ApiProblem;
use Psr\Http\Message\ResponseInterface as Response;

class ErrorHandler {
  /* Quirk to display error JSON style */
  static function errorResponse(Response $response, $message, $status = 401): Response {
    $problem = new ApiProblem($message, "about:blank");
    $problem->setStatus($status);
    
    $body = $response->getBody();
    $body->write($problem->asJson(true));
    
    return $response
      ->withHeader("Content-type", "application/problem+json")
      ->withStatus($status);
  }
}