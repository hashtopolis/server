<?php

namespace Hashtopolis\inc\apiv2\common\error;
use Exception;

class HttpConflict extends Exception {
  public function __construct(string $message = "Resource already exists", int $code = 409) {
    parent::__construct($message, $code);
  }
}