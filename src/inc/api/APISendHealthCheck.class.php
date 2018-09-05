<?php

class APISendHealthCheck extends APIBasic {
  public function execute($QUERY = array()) {
    if (!PQuerySendHealthCheck::isValid($QUERY)) {
      $this->sendErrorResponse(PActions::SEND_HEALTH_CHECK, "Invalid send health check query!");
    }
    $this->checkToken(PActions::SEND_HEALTH_CHECK, $QUERY);
    $this->updateAgent(PActions::SEND_HEALTH_CHECK);

  }
}