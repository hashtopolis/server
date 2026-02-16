<?php

namespace Hashtopolis\inc\api;

use Hashtopolis\inc\agent\PActions;
use Hashtopolis\inc\agent\PQueryGetHealthCheck;
use Hashtopolis\inc\agent\PResponseGetHealthCheck;
use Hashtopolis\inc\agent\PValues;
use Hashtopolis\inc\defines\DConfig;
use Hashtopolis\inc\defines\DServerLog;
use Hashtopolis\dba\Factory;
use Hashtopolis\inc\utils\HealthUtils;
use Hashtopolis\inc\SConfig;

class APIGetHealthCheck extends APIBasic {
  public function execute($QUERY = array()) {
    if (!PQueryGetHealthCheck::isValid($QUERY)) {
      $this->sendErrorResponse(PActions::GET_HEALTH_CHECK, "Invalid get health check query!");
    }
    $this->checkToken(PActions::GET_HEALTH_CHECK, $QUERY);
    $this->updateAgent(PActions::GET_HEALTH_CHECK);
    
    $healthCheckAgent = HealthUtils::checkNeeded($this->agent);
    if ($healthCheckAgent == null) {
      // for whatever reason there is no check available anymore
      $this->sendErrorResponse(PActions::GET_HEALTH_CHECK, "No health check available for this agent!");
    }
    $healthCheck = Factory::getHealthCheckFactory()->get($healthCheckAgent->getHealthCheckId());
    
    DServerLog::log(DServerLog::INFO, "Received health check task", [$this->agent, $healthCheck]);
    
    $hashes = file_get_contents("/tmp/health-check-" . $healthCheck->getId() . ".txt");
    $hashes = explode("\n", $hashes);
    
    $this->sendResponse([
        PResponseGetHealthCheck::ACTION => PActions::GET_HEALTH_CHECK,
        PResponseGetHealthCheck::RESPONSE => PValues::SUCCESS,
        PResponseGetHealthCheck::ATTACK => " --hash-type=" . $healthCheck->getHashtypeId() . " " . $healthCheck->getAttackCmd() . " " . $this->agent->getCmdPars(),
        PResponseGetHealthCheck::CRACKER_BINARY_ID => (int)$healthCheck->getCrackerBinaryId(),
        PResponseGetHealthCheck::HASHES => $hashes,
        PResponseGetHealthCheck::CHECK_ID => (int)$healthCheck->getId(),
        PResponseGetHealthCheck::HASHLIST_ALIAS => SConfig::getInstance()->getVal(DConfig::HASHLIST_ALIAS)
      ]
    );
  }
}