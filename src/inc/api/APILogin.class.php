<?php

use DBA\Agent;
use DBA\Factory;

class APILogin extends APIBasic {
  public function execute($QUERY = array()) {
    if (!PQueryLogin::isValid($QUERY)) {
      $this->sendErrorResponse(PActions::LOGIN, "Invalid login query!");
    }
    $this->checkToken(PActions::LOGIN, $QUERY);
    Factory::getAgentFactory()->set($this->agent, Agent::CLIENT_SIGNATURE, htmlentities($QUERY[PQueryLogin::CLIENT_SIGNATURE], ENT_QUOTES, "UTF-8"));
    $this->updateAgent(PActions::LOGIN);
    
    DServerLog::log(DServerLog::DEBUG, "Agent logged in", [$this->agent]);
    
    $this->sendResponse(array(
        PResponseLogin::ACTION => PActions::LOGIN,
        PResponseLogin::RESPONSE => PValues::SUCCESS,
        PResponseLogin::MULTICAST => (SConfig::getInstance()->getVal(DConfig::MULTICAST_ENABLE)) ? true : false,
        PResponseLogin::TIMEOUT => (int)SConfig::getInstance()->getVal(DConfig::AGENT_TIMEOUT),
        PResponseLogin::VERSION => StartupConfig::getInstance()->getVersion() . " (" . Util::getGitCommit() . ")"
      )
    );
  }
}