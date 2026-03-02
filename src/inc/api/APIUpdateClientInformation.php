<?php

namespace Hashtopolis\inc\api;

use Hashtopolis\inc\agent\PActions;
use Hashtopolis\inc\agent\PQueryUpdateInformation;
use Hashtopolis\inc\agent\PResponse;
use Hashtopolis\inc\agent\PValues;
use Hashtopolis\inc\defines\DServerLog;
use Hashtopolis\dba\models\Agent;
use Hashtopolis\dba\Factory;

class APIUpdateClientInformation extends APIBasic {
  public function execute($QUERY = array()) {
    // check required values and token
    if (!PQueryUpdateInformation::isValid($QUERY)) {
      $this->sendErrorResponse(PActions::UPDATE_CLIENT_INFORMATION, "Invalid update query!");
    }
    $this->checkToken(PActions::UPDATE_CLIENT_INFORMATION, $QUERY);
    
    $devices = $QUERY[PQueryUpdateInformation::DEVICES];
    $uid = htmlentities($QUERY[PQueryUpdateInformation::UID], ENT_QUOTES, "UTF-8");
    $os = intval($QUERY[PQueryUpdateInformation::OPERATING_SYSTEM]);
    
    //determine if the client has cpu only (most likely)
    $cpuOnly = 1;
    foreach ($devices as $device) {
      $device = strtolower($device);
      if (str_contains($device, "amd") || str_contains($device, "ati ") || str_contains($device, "radeon") || str_contains($device, "nvidia") || str_contains($device, "gtx") || str_contains($device, "ti") || strpos($device, "microsoft")) {
        $cpuOnly = 0;
      }
    }
    
    // save agent details
    if (strlen($this->agent->getUid()) == 0 && $this->agent->getCpuOnly() == 0) {
      // we only update this variable on the first time, otherwise we would overwrite manual changes
      Factory::getAgentFactory()->set($this->agent, Agent::CPU_ONLY, $cpuOnly);
    }
    Factory::getAgentFactory()->mset($this->agent, [
        Agent::DEVICES => htmlentities(implode("\n", $devices), ENT_QUOTES, "UTF-8"),
        Agent::UID => $uid,
        Agent::OS => $os
      ]
    );
    
    $this->updateAgent(PActions::UPDATE_CLIENT_INFORMATION);
    DServerLog::log(DServerLog::DEBUG, "Agent sent updated client information", [$this->agent]);
    
    $this->sendResponse(array(
        PQueryUpdateInformation::ACTION => PActions::UPDATE_CLIENT_INFORMATION,
        PResponse::RESPONSE => PValues::SUCCESS
      )
    );
  }
}
