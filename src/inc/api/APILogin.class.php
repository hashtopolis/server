<?php

class APILogin extends APIBasic {
  public function execute($QUERY = array()) {
    if (!PQueryLogin::isValid($QUERY)) {
      $this->sendErrorResponse(PActions::LOGIN, "Invalid login query!");
    }
    $this->checkToken(PActions::LOGIN, $QUERY);
    $this->agent->setClientSignature(htmlentities($QUERY[PQueryLogin::CLIENT_SIGNATURE], ENT_QUOTES, "UTF-8"));
    $this->updateAgent(PActions::LOGIN);
    
    $this->sendResponse(array(
        PResponseLogin::ACTION => PActions::LOGIN,
        PResponseLogin::RESPONSE => PValues::SUCCESS,
        PResponseLogin::TIMEOUT => SConfig::getInstance()->getVal(DConfig::AGENT_TIMEOUT)
      )
    );
  }
}