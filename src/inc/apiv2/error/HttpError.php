<?php

namespace Hashtopolis\inc\apiv2\error;
use Exception;

class HttpError extends Exception {
  public function __construct(string $message = "Bad request", int $code = 400) {
    parent::__construct($message, $code);
  }
}