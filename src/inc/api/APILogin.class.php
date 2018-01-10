<?php

class APILogin extends APIBasic {
  public function execute($QUERY = array()) {
    /** @var DataSet $CONFIG */
    global $FACTORIES, $CONFIG;
    
    if (!PQueryLogin::isValid($QUERY)) {
      $this->sendErrorResponse(PActions::LOGIN, "Invalid login query!");
    }
    $this->checkToken(PActions::LOGIN, $QUERY);
    $this->updateAgent(PActions::LOGIN);
    
    $this->sendResponse(array(
        PResponseLogin::ACTION => PActions::LOGIN,
        PResponseLogin::RESPONSE => PValues::SUCCESS,
        PResponseLogin::TIMEOUT => $CONFIG->getVal(DConfig::AGENT_TIMEOUT)
      )
    );
  }
}