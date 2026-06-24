<?php

namespace Hashtopolis\inc\api;


use Hashtopolis\inc\agent\PActions;
use Hashtopolis\inc\agent\PResponse;
use Hashtopolis\inc\agent\PValues;

class APITestConnection extends APIBasic {
  public function execute($QUERY = array()) {
    $this->sendResponse(array(
        PResponse::ACTION => PActions::TEST_CONNECTION,
        PResponse::RESPONSE => PValues::SUCCESS
      )
    );
  }
}