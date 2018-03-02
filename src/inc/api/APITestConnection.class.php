<?php

class APITestConnection extends APIBasic {
  public function execute($QUERY = array()) {
    $this->sendResponse(array(
        PResponse::ACTION => PActions::TEST_CONNECTION,
        PResponse::RESPONSE => PValues::SUCCESS
      )
    );
  }
}