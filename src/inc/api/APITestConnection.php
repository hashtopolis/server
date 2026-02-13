<?php

namespace Hashtopolis\inc\api;

use PActions;
use PResponse;
use PValues;

class APITestConnection extends APIBasic {
  public function execute($QUERY = array()) {
    $this->sendResponse(array(
        PResponse::ACTION => PActions::TEST_CONNECTION,
        PResponse::RESPONSE => PValues::SUCCESS
      )
    );
  }
}