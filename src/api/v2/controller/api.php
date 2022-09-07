<?php

namespace APIv2;

require_once __DIR__ . '/../auth/authentication.php';

use APIv2\Authentication;

class ApiController {

  protected function _user() {
    return Authentication::instance()->user();
  }
}