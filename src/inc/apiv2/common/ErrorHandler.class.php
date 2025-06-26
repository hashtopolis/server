<?php

use Crell\ApiProblem\ApiProblem;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface as Response;

/* Quirk to display error JSON style */
function errorResponse(Response $response, $message, $status = 401): MessageInterface|Response {
  $problem = new ApiProblem($message, "about:blank");
  $problem->setStatus($status);
  
  $body = $response->getBody();
  $body->write($problem->asJson(true));
  
  return $response
    ->withHeader("Content-type", "application/problem+json")
    ->withStatus($status);
}

class ResourceNotFoundError extends Exception {
  public function __construct(string $message = "Resource not found", int $code = 404) {
    parent::__construct($message, $code);
  }
}

class HttpError extends Exception {
  public function __construct(string $message = "Bad request", int $code = 400) {
    parent::__construct($message, $code);
  }
}

class HttpForbidden extends Exception {
  public function __construct(string $message = "Forbidden", int $code = 403) {
    parent::__construct($message, $code);
  }
}

class HttpConflict extends Exception {
  public function __construct(string $message = "Resource already exists", int $code = 409) {
    parent::__construct($message, $code);
  }
}

class InternalError extends Exception {
  public function __construct(string $message = "Internal error", int $code = 500) {
    parent::__construct($message, $code);
  }
}
