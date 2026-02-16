<?php

namespace Hashtopolis\inc\api;

use Hashtopolis\inc\agent\PActions;
use Hashtopolis\inc\agent\PQueryRegister;
use Hashtopolis\inc\agent\PResponseRegister;
use Hashtopolis\inc\agent\PValues;
use Hashtopolis\inc\defines\DConfig;
use Hashtopolis\inc\defines\DNotificationType;
use Hashtopolis\inc\defines\DPayloadKeys;
use Hashtopolis\inc\utils\AccessUtils;
use Hashtopolis\inc\DataSet;
use Hashtopolis\inc\defines\DServerLog;
use Hashtopolis\dba\models\AccessGroupAgent;
use Hashtopolis\dba\models\Agent;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\dba\models\RegVoucher;
use Hashtopolis\dba\Factory;
use Hashtopolis\inc\handlers\NotificationHandler;
use Hashtopolis\inc\SConfig;
use Hashtopolis\inc\Util;

class APIRegisterAgent extends APIBasic {
  public function execute($QUERY = array()) {
    //check required values
    if (!PQueryRegister::isValid($QUERY)) {
      $this->sendErrorResponse(PActions::REGISTER, "Invalid registering query!");
    }
    
    $qF = new QueryFilter(RegVoucher::VOUCHER, $QUERY[PQueryRegister::VOUCHER], "=");
    $voucher = Factory::getRegVoucherFactory()->filter([Factory::FILTER => $qF], true);
    if ($voucher == null) {
      $this->sendErrorResponse(PActions::REGISTER, "Provided voucher does not exist.");
    }
    
    $name = htmlentities($QUERY[PQueryRegister::AGENT_NAME], ENT_QUOTES, "UTF-8");
    
    $cpuOnly = 0;
    if (isset($QUERY[PQueryRegister::CPU_ONLY]) && $QUERY[PQueryRegister::CPU_ONLY] == true) {
      $cpuOnly = 1;
    }
    
    //create access token & save agent details
    $token = Util::randomString(10);
    $agent = new Agent(null, $name, "", -1, "", "", 0, 1, 0, $token, PActions::REGISTER, time(), Util::getIP(), null, $cpuOnly, "");
    
    if (SConfig::getInstance()->getVal(DConfig::VOUCHER_DELETION) == 0) {
      Factory::getRegVoucherFactory()->delete($voucher);
    }
    $agent = Factory::getAgentFactory()->save($agent);
    if ($agent != null) {
      $payload = new DataSet(array(DPayloadKeys::AGENT => $agent));
      NotificationHandler::checkNotifications(DNotificationType::NEW_AGENT, $payload);
      DServerLog::log(DServerLog::INFO, "Registered new agent", [$agent]);
      
      // assign agent to default group
      $accessGroup = AccessUtils::getOrCreateDefaultAccessGroup();
      $accessGroupAgent = new AccessGroupAgent(null, $accessGroup->getId(), $agent->getId());
      Factory::getAccessGroupAgentFactory()->save($accessGroupAgent);
      DServerLog::log(DServerLog::INFO, "Assigned agent to access group", [$agent, $accessGroup]);
      
      $this->sendResponse(array(
          PQueryRegister::ACTION => PActions::REGISTER,
          PResponseRegister::RESPONSE => PValues::SUCCESS,
          PResponseRegister::TOKEN => $token
        )
      );
    }
    else {
      DServerLog::log(DServerLog::ERROR, "Saving of agent failed!", [$agent]);
      $this->sendErrorResponse(PActions::REGISTER, "Could not register you to server: Saving failed!");
    }
  }
}
