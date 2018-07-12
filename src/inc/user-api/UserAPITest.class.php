<?php

class UserAPITest extends UserAPIBasic {
  public function execute($QUERY = array()) {
    switch($QUERY[UQuery::REQUEST]){
      case USectionTest::CONNECTION:
        $this->sendResponse(array(
            UResponse::SECTION => USection::TEST,
            UResponse::REQUEST => USectionTest::CONNECTION,
            UResponse::RESPONSE => PValues::SUCCESS
          )
        );
        break;
    }
  }
}