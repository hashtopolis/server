<?php

namespace Hashtopolis\inc\api;

use Exception;
use Hashtopolis\inc\agent\PActions;
use Hashtopolis\inc\agent\PQueryLogin;
use Hashtopolis\inc\agent\PResponse;
use Hashtopolis\inc\agent\PResponseLogin;
use Hashtopolis\inc\agent\PValues;
use Hashtopolis\inc\defines\DConfig;
use Hashtopolis\inc\defines\DServerLog;
use Hashtopolis\dba\models\Agent;
use Hashtopolis\dba\Factory;
use Hashtopolis\inc\SConfig;
use Hashtopolis\inc\StartupConfig;
use Hashtopolis\inc\Util;

class APILogin extends APIBasic {
  /**
   * @throws Exception
   */
  public function execute(array $QUERY = array()): void {
    if (!PQueryLogin::isValid($QUERY)) {
      $this->sendErrorResponse(PActions::LOGIN, "Invalid login query!");
    }
    $this->checkToken(PActions::LOGIN, $QUERY);
    $this->agent = Factory::getAgentFactory()->set($this->agent, Agent::CLIENT_SIGNATURE, htmlentities($QUERY[PQueryLogin::CLIENT_SIGNATURE], ENT_QUOTES, "UTF-8"));
    $this->updateAgent(PActions::LOGIN);
    
    DServerLog::log(DServerLog::DEBUG, "Agent logged in", [$this->agent]);
    
    $this->sendResponse(array(
        PResponse::ACTION => PActions::LOGIN,
        PResponse::RESPONSE => PValues::SUCCESS,
        PResponseLogin::MULTICAST => (bool)SConfig::getInstance()->getVal(DConfig::MULTICAST_ENABLE),
        PResponseLogin::TIMEOUT => (int)SConfig::getInstance()->getVal(DConfig::AGENT_TIMEOUT),
        PResponseLogin::VERSION => StartupConfig::getInstance()->getVersion() . " (" . Util::getGitCommit() . ")"
      )
    );
  }
}