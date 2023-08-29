<?php

use DBA\Agent;
use DBA\ContainFilter;
use DBA\AccessGroupAgent;
use DBA\OrderFilter;
use DBA\Factory;

class UserAPIAgent extends UserAPIBasic {
  public function execute($QUERY = array()) {
    try {
      switch ($QUERY[UQuery::REQUEST]) {
        case USectionAgent::CREATE_VOUCHER:
          $this->createVoucher($QUERY);
          break;
        case USectionAgent::GET_BINARIES:
          $this->getBinaries();
          break;
        case USectionAgent::DELETE_VOUCHER:
          $this->deleteVoucher($QUERY);
          break;
        case USectionAgent::LIST_VOUCHERS:
          $this->listVouchers();
          break;
        case USectionAgent::LIST_AGENTS:
          $this->listAgents();
          break;
        case USectionAgent::GET:
          $this->getAgent($QUERY);
          break;
        case USectionAgent::SET_ACTIVE:
          $this->setActive($QUERY);
          break;
        case USectionAgent::CHANGE_OWNER:
          $this->changeOwner($QUERY);
          break;
        case USectionAgent::SET_NAME:
          $this->setName($QUERY);
          break;
        case USectionAgent::SET_CPU_ONLY:
          $this->setCpuOnly($QUERY);
          break;
        case USectionAgent::SET_EXTRA_PARAMS:
          $this->setExtraParams($QUERY);
          break;
        case USectionAgent::SET_ERROR_FLAG:
          $this->setError($QUERY);
          break;
        case USectionAgent::SET_TRUSTED:
          $this->setTrusted($QUERY);
          break;
        case USectionAgent::DELETE_AGENT:
          $this->deleteAgent($QUERY);
          break;
        default:
          $this->sendErrorResponse($QUERY[UQuery::SECTION], "INV", "Invalid section request!");
      }
    }
    catch (HTException $e) {
      $this->sendErrorResponse($QUERY[UQuery::SECTION], $QUERY[UQuery::REQUEST], $e->getMessage());
    }
  }
  
  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function deleteAgent($QUERY) {
    if (!isset($QUERY[UQueryAgent::AGENT_ID])) {
      throw new HTException("Invalid query!");
    }
    AgentUtils::delete($QUERY[UQueryAgent::AGENT_ID], $this->user);
    $this->sendSuccessResponse($QUERY);
  }
  
  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function getAgent($QUERY) {
    $agent = AgentUtils::getAgent($QUERY[UQueryAgent::AGENT_ID], $this->user);
    $devices = HardwareGroupUtils::getDevicesForAgent($agent);
    $response = [
      UResponseAgent::SECTION => $QUERY[UQueryAgent::SECTION],
      UResponseAgent::REQUEST => $QUERY[UQueryAgent::REQUEST],
      UResponseAgent::RESPONSE => UValues::OK,
      UResponseAgent::AGENT_NAME => $agent->getAgentName(),
      UResponseAgent::AGENT_DEVICES => explode("\n", $devices),
      UResponseAgent::AGENT_OWNER => [
        UResponseAgent::AGENT_OWNER_ID => (int)$agent->getUserId(),
        UResponseAgent::AGENT_OWNER_NAME => Util::getUsernameById($agent->getUserId())
      ],
      UResponseAgent::AGENT_CPU_ONLY => ($agent->getCpuOnly() == 1) ? true : false,
      UResponseAgent::AGENT_TRUSTED => ($agent->getIsTrusted() == 1) ? true : false,
      UResponseAgent::AGENT_ACTIVE => ($agent->getIsActive() == 1) ? true : false,
      UResponseAgent::AGENT_TOKEN => $agent->getToken(),
      UResponseAgent::AGENT_PARAMS => $agent->getCmdPars(),
      UResponseAgent::AGENT_ERRORS => (int)$agent->getIgnoreErrors(),
      UResponseAgent::AGENT_ACTIVITY => [
        UResponseAgent::AGENT_ACTIVITY_ACTION => $agent->getLastAct(),
        UResponseAgent::AGENT_ACTIVITY_TIME => (int)$agent->getLastTime(),
        UResponseAgent::AGENT_ACTIVITY_IP => (SConfig::getInstance()->getVal(DConfig::HIDE_IP_INFO) == 1) ? "Hidden" : $agent->getLastIp()
      ]
    ];
    $this->sendResponse($response);
  }
  
  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function setTrusted($QUERY) {
    if (!isset($QUERY[UQueryAgent::TRUSTED]) || !isset($QUERY[UQueryAgent::AGENT_ID]) || !is_bool($QUERY[UQueryAgent::TRUSTED])) {
      throw new HTException("Invalid query!");
    }
    AgentUtils::setTrusted($QUERY[UQueryAgent::AGENT_ID], $QUERY[UQueryAgent::TRUSTED], $this->user);
    $this->sendSuccessResponse($QUERY);
  }
  
  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function setError($QUERY) {
    if (!isset($QUERY[UQueryAgent::AGENT_ID]) || !isset($QUERY[UQueryAgent::IGNORE_ERRORS]) || !is_numeric($QUERY[UQueryAgent::IGNORE_ERRORS]) || !in_array($QUERY[UQueryAgent::IGNORE_ERRORS], [0, 1, 2])) {
      throw new HTException("Invalid query!");
    }
    AgentUtils::changeIgnoreErrors($QUERY[UQueryAgent::AGENT_ID], $QUERY[UQueryAgent::IGNORE_ERRORS], $this->user);
    $this->sendSuccessResponse($QUERY);
  }
  
  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function setExtraParams($QUERY) {
    if (!isset($QUERY[UQueryAgent::EXTRA_PARAMS])) {
      throw new HTException("Invalid query!");
    }
    AgentUtils::changeCmdParameters($QUERY[UQueryAgent::AGENT_ID], $QUERY[UQueryAgent::EXTRA_PARAMS], $this->user);
    $this->sendSuccessResponse($QUERY);
  }
  
  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function setCpuOnly($QUERY) {
    if (!isset($QUERY[UQueryAgent::CPU_ONLY]) || !isset($QUERY[UQueryAgent::AGENT_ID]) || !is_bool($QUERY[UQueryAgent::CPU_ONLY])) {
      throw new HTException("Invalid query!");
    }
    AgentUtils::setAgentCpu($QUERY[UQueryAgent::AGENT_ID], $QUERY[UQueryAgent::CPU_ONLY], $this->user);
    $this->sendSuccessResponse($QUERY);
  }
  
  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function setName($QUERY) {
    if (!isset($QUERY[UQueryAgent::AGENT_ID]) || !isset($QUERY[UQueryAgent::NAME]) || strlen($QUERY[UQueryAgent::NAME]) == 0) {
      throw new HTException("Invalid query!");
    }
    AgentUtils::rename($QUERY[UQueryAgent::AGENT_ID], $QUERY[UQueryAgent::NAME], $this->user);
    $this->sendSuccessResponse($QUERY);
  }
  
  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function changeOwner($QUERY) {
    if (!isset($QUERY[UQueryAgent::USER]) || !isset($QUERY[UQueryAgent::AGENT_ID])) {
      throw new HTException("Invalid query!");
    }
    AgentUtils::changeOwner($QUERY[UQueryAgent::AGENT_ID], $QUERY[UQueryAgent::USER], $this->user);
    $this->sendSuccessResponse($QUERY);
  }
  
  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function setActive($QUERY) {
    if (!isset($QUERY[UQueryAgent::ACTIVE]) || !is_bool($QUERY[UQueryAgent::ACTIVE])) {
      throw new HTException("Invalid query!");
    }
    AgentUtils::setActive($QUERY[UQueryAgent::AGENT_ID], $QUERY[UQueryAgent::ACTIVE], $this->user);
    $this->sendSuccessResponse($QUERY);
  }
  
  private function listAgents() {
    $accessGroups = AccessUtils::getAccessGroupsOfUser($this->user);
    
    $qF = new ContainFilter(AccessGroupAgent::ACCESS_GROUP_ID, Util::arrayOfIds($accessGroups));
    $accessGroupAgents = Factory::getAccessGroupAgentFactory()->filter([Factory::FILTER => $qF]);
    $agentIds = array();
    foreach ($accessGroupAgents as $accessGroupAgent) {
      $agentIds[] = $accessGroupAgent->getAgentId();
    }
    
    $oF = new OrderFilter(Agent::AGENT_ID, "ASC", Factory::getAgentFactory());
    $qF = new ContainFilter(Agent::AGENT_ID, $agentIds);
    $agents = Factory::getAgentFactory()->filter([Factory::FILTER => $qF, Factory::ORDER => $oF]);
    $arr = [];
    foreach ($agents as $agent) {
      $devices = HardwareGroupUtils::getDevicesForAgent($agent);

      $arr[] = array(
        UResponseAgent::AGENTS_ID => $agent->getId(),
        UResponseAgent::AGENTS_NAME => $agent->getAgentName(),
        UResponseAgent::AGENTS_DEVICES => explode("\n", $devices)
      );
    }
    $this->sendResponse(array(
        UResponseAgent::SECTION => USection::AGENT,
        UResponseAgent::REQUEST => USectionAgent::LIST_AGENTS,
        UResponseAgent::RESPONSE => UValues::OK,
        UResponseAgent::AGENTS => $arr
      )
    );
  }
  
  /**
   * @param array $QUERY
   * @throws HTException
   */
  private function deleteVoucher($QUERY) {
    if (!isset($QUERY[UQueryAgent::VOUCHER])) {
      throw new HTException("Invalid delete voucher query!");
    }
    AgentUtils::deleteVoucher($QUERY[UQueryAgent::VOUCHER]);
    $this->sendSuccessResponse($QUERY);
  }
  
  private function listVouchers() {
    $vouchers = Factory::getRegVoucherFactory()->filter([]);
    $arr = [];
    foreach ($vouchers as $voucher) {
      $arr[] = $voucher->getVoucher();
    }
    $this->sendResponse(array(
        UResponseAgent::SECTION => USection::AGENT,
        UResponseAgent::REQUEST => USectionAgent::GET_BINARIES,
        UResponseAgent::RESPONSE => UValues::OK,
        UResponseAgent::VOUCHERS => $arr
      )
    );
  }
  
  private function getBinaries() {
    $url = explode("/", $_SERVER['PHP_SELF']);
    unset($url[sizeof($url) - 1]);
    unset($url[sizeof($url) - 1]);
    $agentUrl = Util::buildServerUrl() . implode("/", $url) . "/api/server.php";
    $baseUrl = Util::buildServerUrl() . implode("/", $url) . "/agents.php?download=";
    $response = array(
      UResponseAgent::SECTION => USection::AGENT,
      UResponseAgent::REQUEST => USectionAgent::GET_BINARIES,
      UResponseAgent::RESPONSE => UValues::OK,
      UResponseAgent::AGENT_URL => $agentUrl
    );
    
    $arr = [];
    $binaries = Factory::getAgentBinaryFactory()->filter([]);
    foreach ($binaries as $binary) {
      $arr[] = array(
        UResponseAgent::BINARIES_NAME => $binary->getType(),
        UResponseAgent::BINARIES_OS => $binary->getOperatingSystems(),
        UResponseAgent::BINARIES_URL => $baseUrl . $binary->getId(),
        UResponseAgent::BINARIES_VERSION => $binary->getVersion(),
        UResponseAgent::BINARIES_FILENAME => $binary->getFilename()
      );
    }
    $response[UResponseAgent::BINARIES] = $arr;
    $this->sendResponse($response);
  }
  
  /**
   * @param $QUERY
   * @throws HTException
   */
  private function createVoucher($QUERY) {
    $voucher = Util::randomString(10);
    if (isset($QUERY[UQueryAgent::VOUCHER])) {
      $voucher = $QUERY[UQueryAgent::VOUCHER];
    }
    AgentUtils::createVoucher($voucher);
    $response = array(
      UResponseAgent::SECTION => USection::AGENT,
      UResponseAgent::REQUEST => USectionAgent::CREATE_VOUCHER,
      UResponseAgent::RESPONSE => UValues::OK,
      UResponseAgent::VOUCHER => $voucher
    );
    $this->sendResponse($response);
  }
}