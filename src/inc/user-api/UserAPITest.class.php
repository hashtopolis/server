<?php

use DBA\ApiKey;
use DBA\QueryFilter;

class UserAPITest extends UserAPIBasic {
  public function execute($QUERY = array()) {
    switch ($QUERY[UQuery::REQUEST]) {
      case USectionTest::CONNECTION:
        $this->connectionTest($QUERY);
        break;
      case USectionTest::ACCESS:
        $this->accessTest($QUERY);
        break;
      default:
        $this->sendErrorResponse($QUERY[UQuery::SECTION], "INV", "Invalid section request!");
    }
  }
  
  private function connectionTest($QUERY) {
    $this->sendResponse(array(
        UResponse::SECTION => USection::TEST,
        UResponse::REQUEST => USectionTest::CONNECTION,
        UResponse::RESPONSE => UValues::SUCCESS
      )
    );
  }
  
  private function accessTest($QUERY) {
    global $FACTORIES;
    
    $qF = new QueryFilter(ApiKey::ACCESS_KEY, $QUERY[UQuery::ACCESS_KEY], "=");
    $apiKey = $FACTORIES::getApiKeyFactory()->filter(array($FACTORIES::FILTER => $qF), true);
    if ($apiKey == null) {
      $this->sendResponse(array(
          UResponseErrorMessage::SECTION => USection::TEST,
          UResponseErrorMessage::REQUEST => USectionTest::ACCESS,
          UResponseErrorMessage::RESPONSE => UValues::ERROR,
          UResponseErrorMessage::MESSAGE => "API key was not found!"
        )
      );
    }
    else if ($apiKey->getStartValid() > time() || $apiKey->getEndValid() < time()) {
      $this->sendResponse(array(
          UResponseErrorMessage::SECTION => USection::TEST,
          UResponseErrorMessage::REQUEST => USectionTest::ACCESS,
          UResponseErrorMessage::RESPONSE => UValues::ERROR,
          UResponseErrorMessage::MESSAGE => "API key is not valid yet or has expired!"
        )
      );
    }
    $this->sendResponse(array(
        UResponse::SECTION => USection::TEST,
        UResponse::REQUEST => USectionTest::ACCESS,
        UResponse::RESPONSE => UValues::OK
      )
    );
  }
}