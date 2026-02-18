<?php

namespace Hashtopolis\inc\user_api;

use Hashtopolis\dba\models\ApiKey;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\dba\Factory;
use Hashtopolis\inc\defines\UQuery;
use Hashtopolis\inc\defines\UResponse;
use Hashtopolis\inc\defines\UResponseErrorMessage;
use Hashtopolis\inc\defines\USection;
use Hashtopolis\inc\defines\USectionTest;
use Hashtopolis\inc\defines\UValues;

class UserAPITest extends UserAPIBasic {
  public function execute($QUERY = array()) {
    switch ($QUERY[UQuery::REQUEST]) {
      case USectionTest::CONNECTION:
        $this->connectionTest();
        break;
      case USectionTest::ACCESS:
        $this->accessTest($QUERY);
        break;
      default:
        $this->sendErrorResponse($QUERY[UQuery::SECTION], "INV", "Invalid section request!");
    }
  }
  
  private function connectionTest() {
    $this->sendResponse(array(
        UResponse::SECTION => USection::TEST,
        UResponse::REQUEST => USectionTest::CONNECTION,
        UResponse::RESPONSE => UValues::SUCCESS
      )
    );
  }
  
  private function accessTest($QUERY) {
    $qF = new QueryFilter(ApiKey::ACCESS_KEY, $QUERY[UQuery::ACCESS_KEY], "=");
    $apiKey = Factory::getApiKeyFactory()->filter([Factory::FILTER => $qF], true);
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