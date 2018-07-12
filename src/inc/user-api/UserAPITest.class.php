<?php
use DBA\ApiKey;
use DBA\QueryFilter;

class UserAPITest extends UserAPIBasic {
  public function execute($QUERY = array()) {
    global $FACTORIES;

    switch($QUERY[UQuery::REQUEST]){
      case USectionTest::CONNECTION:
        $this->connectionTest($QUERY);
        break;
      case USectionTest::ACCESS:
        $this->accessTest($QUERY);
        break;
    }
  }

  private function connectionTest($QUERY){
    $this->sendResponse(array(
        UResponse::SECTION => USection::TEST,
        UResponse::REQUEST => USectionTest::CONNECTION,
        UResponse::RESPONSE => UValues::SUCCESS
      )
    );
  }

  private function accessTest($QUERY){
    global $FACTORIES;

    $qF = new QueryFilter(ApiKey::ACCESS_KEY, $QUERY[UQuery::ACCESS_KEY], "=");
    $apiKey = $FACTORIES::getApiKeyFactory()->filter(array($FACTORIES::FILTER => $qF), true);
    if($apiKey == null){
      $this->sendResponse(array(
          UResponse::SECTION => USection::TEST,
          UResponse::REQUEST => USectionTest::ACCESS,
          UResponse::RESPONSE => UValuesAccess::NOT_FOUND
        )
      );
    }
    else if($apiKey->getStartValid() > time() || $apiKey->getEndValid() < time()){
      $this->sendResponse(array(
          UResponse::SECTION => USection::TEST,
          UResponse::REQUEST => USectionTest::ACCESS,
          UResponse::RESPONSE => UValuesAccess::EXPIRED
        )
      );
    }
    $this->sendResponse(array(
        UResponse::SECTION => USection::TEST,
        UResponse::REQUEST => USectionTest::ACCESS,
        UResponse::RESPONSE => UValuesAccess::OK
      )
    );
  }
}