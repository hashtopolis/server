<?php

class APIGetHealthCheck extends APIBasic {
  public function execute($QUERY = array()) {
    if (!PQueryGetHealthCheck::isValid($QUERY)) {
      $this->sendErrorResponse(PActions::GET_HEALTH_CHECK, "Invalid get health check query!");
    }
    $this->checkToken(PActions::GET_HEALTH_CHECK, $QUERY);
    $this->updateAgent(PActions::GET_HEALTH_CHECK);

  }
}