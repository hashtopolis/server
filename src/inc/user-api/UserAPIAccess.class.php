<?php

class UserAPIAccess extends UserAPIBasic {
  public function execute($QUERY = array()) {
    switch ($QUERY[UQuery::REQUEST]) {
      default:
        $this->sendErrorResponse($QUERY[UQuery::SECTION], "INV", "Invalid section request!");
    }
  }
}