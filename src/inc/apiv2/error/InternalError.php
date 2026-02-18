<?php

namespace Hashtopolis\inc\apiv2\error;
use Exception;

class InternalError extends Exception {
  public function __construct(string $message = "Internal error", int $code = 500) {
    parent::__construct($message, $code);
  }
}