<?php

namespace Hashtopolis\inc\apiv2\common\error;

use Exception;

class ResourceNotFoundError extends Exception {
  public function __construct(string $message = "Resource not found", int $code = 404) {
    parent::__construct($message, $code);
  }
}