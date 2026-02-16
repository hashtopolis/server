<?php

namespace Hashtopolis\inc\api;

use Hashtopolis\inc\agent\PActions;
use Hashtopolis\inc\agent\PQueryDeRegister;
use Hashtopolis\inc\agent\PResponseDeRegister;
use Hashtopolis\inc\agent\PValues;
use Hashtopolis\inc\defines\DConfig;
use Hashtopolis\inc\utils\AgentUtils;
use Hashtopolis\inc\HTException;
use Hashtopolis\inc\SConfig;

class APIDeRegisterAgent extends APIBasic {
  public function execute($QUERY = array()) {
    //check required values
    if (!PQueryDeRegister::isValid($QUERY)) {
      $this->sendErrorResponse(PActions::DEREGISTER, "Invalid de-registering query!");
    }
    $this->checkToken(PActions::DEREGISTER, $QUERY);
    
    if (!SConfig::getInstance()->getVal(DConfig::ALLOW_DEREGISTER)) {
      $this->sendErrorResponse(PActions::DEREGISTER, "De-registration is not allowed on this server!");
    }
    try {
      AgentUtils::delete($this->agent->getId(), null);
    }
    catch (HTException $e) {
      $this->sendErrorResponse(PActions::DEREGISTER, "Error occured during de-registration: " . $e->getMessage());
    }
    $this->sendResponse(array(
        PQueryDeRegister::ACTION => PActions::DEREGISTER,
        PResponseDeRegister::RESPONSE => PValues::SUCCESS
      )
    );
  }
}