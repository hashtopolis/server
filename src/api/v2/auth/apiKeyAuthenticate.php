<?php

namespace APIv2;

require_once __DIR__ . '/../../../inc/load.php';
require_once __DIR__ . '/authentication.php';

use Luracast\Restler\iAuthenticate;

class ApiKeyAuthenticate implements iAuthenticate {

  public function __getWWWAuthenticateString() {
    return 'Query name="api_key"';
  }

  public function __isAllowed() {
    if (!isset($_GET['api_key'])) {
      return false;
    }
    // TODO: check permission for specific request
    return Authentication::instance()->authenticateWithApiKey($_GET['api_key']);
  }
}