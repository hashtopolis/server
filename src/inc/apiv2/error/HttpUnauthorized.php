<?php

namespace Hashtopolis\inc\apiv2\error;
use Exception;

/**
 * The request lacks valid authentication credentials. Clients are expected to react by
 * authenticating again, which is what separates this from HttpForbidden (403): re-authenticating
 * would not help there.
 */
class HttpUnauthorized extends Exception {
  public function __construct(string $message = "Unauthorized", int $code = 401) {
    parent::__construct($message, $code);
  }
}
