<?php

namespace Hashtopolis\inc\apiv2\error;
use Exception;

class HttpForbidden extends Exception {
  public function __construct(string $message = "Forbidden", int $code = 403) {
    parent::__construct($message, $code);
  }
}