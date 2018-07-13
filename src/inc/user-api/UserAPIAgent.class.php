<?php
use DBA\Agent;
use DBA\QueryFilter;
use DBA\RegVoucher;
use DBA\ContainFilter;
use DBA\AccessGroupAgent;
use DBA\OrderFilter;
use DBA\User;

class UserAPIAgent extends UserAPIBasic {
  public function execute($QUERY = array()) {
    global $FACTORIES;

    switch($QUERY[UQuery::REQUEST]){
      case USectionAgent::CREATE_VOUCHER:
        $this->createVoucher($QUERY);
        break;
      case USectionAgent::GET_BINARIES:
        $this->getBinaries($QUERY);
        break;
      case USectionAgent::DELETE_VOUCHER:
        $this->deleteVoucher($QUERY);
        break;
      case USectionAgent::LIST_VOUCHERS:
        $this->listVouchers($QUERY);
        break;
      case USectionAgent::LIST_AGENTS:
        $this->listAgents($QUERY);
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
      default:
        $this->sendErrorResponse($QUERY[UQuery::SECTION], "INV", "Invalid section request!");
    }
  }

  private function getAgent($QUERY){
    global $FACTORIES, $CONFIG;

    $agent = $this->checkAgent($QUERY);
    $response = [
      UResponseAgent::SECTION => $QUERY[UQueryAgent::SECTION],
      UResponseAgent::REQUEST => $QUERY[UQueryAgent::REQUEST],
      UResponseAgent::RESPONSE => UValues::OK,
      UResponseAgent::AGENT_NAME => $agent->getAgentName(),
      UResponseAgent::AGENT_DEVICES => explode("\n", $agent->getDevices()),
      UResponseAgent::AGENT_OWNER => [
        UResponseAgent::AGENT_OWNER_ID => (int)$agent->getUserId(),
        UResponseAgent::AGENT_OWNER_NAME => Util::getUsernameById($agent->getUserId())
      ],
      UResponseAgent::AGENT_CPU_ONLY => ($agent->getCpuOnly() == 1)?true:false,
      UResponseAgent::AGENT_TRUSTED => ($agent->getIsTrusted() == 1)?true:false,
      UResponseAgent::AGENT_ACTIVE => ($agent->getIsActive() == 1)?true:false,
      UResponseAgent::AGENT_TOKEN => $agent->getToken(),
      UResponseAgent::AGENT_PARAMS => $agent->getCmdPars(),
      UResponseAgent::AGENT_ERRORS => (int)$agent->getIgnoreErrors(),
      UResponseAgent::AGENT_ACTIVITY => [
        UResponseAgent::AGENT_ACTIVITY_ACTION => $agent->getLastAct(),
        UResponseAgent::AGENT_ACTIVITY_TIME => (int)$agent->getLastTime(),
        UResponseAgent::AGENT_ACTIVITY_IP => ($CONFIG->getVal(DConfig::HIDE_IP_INFO) == 1)?"Hidden":$agent->getLastIp()
      ]
    ];
    $this->sendResponse($response);
  }

  private function setTrusted($QUERY){
    global $FACTORIES;

    if(!isset($QUERY[UQueryAgent::TRUSTED]) || !is_bool($QUERY[UQueryAgent::TRUSTED])){
      $this->sendErrorResponse($QUERY[UQueryAgent::SECTION], $QUERY[UQueryAgent::REQUEST], "Invalid query!");
    }
    $agent = $this->checkAgent($QUERY);
    $agent->setIsTrusted(($QUERY[UQueryAgent::TRUSTED])?1:0);
    $FACTORIES::getAgentFactory()->update($agent);
    $this->sendSuccessResponse($QUERY);
  }

  private function setError($QUERY){
    global $FACTORIES;

    if(!isset($QUERY[UQueryAgent::IGNORE_ERRORS])  || !is_numeric($QUERY[UQueryAgent::IGNORE_ERRORS]) || !in_array($QUERY[UQueryAgent::IGNORE_ERRORS], [0, 1, 2])){
      $this->sendErrorResponse($QUERY[UQueryAgent::SECTION], $QUERY[UQueryAgent::REQUEST], "Invalid query!");
    }
    $agent = $this->checkAgent($QUERY);
    $agent->setIgnoreErrors($QUERY[UQueryAgent::IGNORE_ERRORS]);
    $FACTORIES::getAgentFactory()->update($agent);
    $this->sendSuccessResponse($QUERY);
  }

  private function setExtraParams($QUERY){
    global $FACTORIES;

    if(!isset($QUERY[UQueryAgent::EXTRA_PARAMS])){
      $this->sendErrorResponse($QUERY[UQueryAgent::SECTION], $QUERY[UQueryAgent::REQUEST], "Invalid query!");
    }
    $agent = $this->checkAgent($QUERY);
    $agent->setCmdPars($QUERY[UQueryAgent::EXTRA_PARAMS]);
    $FACTORIES::getAgentFactory()->update($agent);
    $this->sendSuccessResponse($QUERY);
  }

  private function setCpuOnly($QUERY){
    global $FACTORIES;

    if(!isset($QUERY[UQueryAgent::CPU_ONLY]) || !is_bool($QUERY[UQueryAgent::CPU_ONLY])){
      $this->sendErrorResponse($QUERY[UQueryAgent::SECTION], $QUERY[UQueryAgent::REQUEST], "Invalid query!");
    }
    $agent = $this->checkAgent($QUERY);
    $agent->setCpuOnly(($QUERY[UQueryAgent::CPU_ONLY])?1:0);
    $FACTORIES::getAgentFactory()->update($agent);
    $this->sendSuccessResponse($QUERY);
  }

  private function setName($QUERY){
    global $FACTORIES;

    if(!isset($QUERY[UQueryAgent::NAME]) || strlen($QUERY[UQueryAgent::NAME]) == 0){
      $this->sendErrorResponse($QUERY[UQueryAgent::SECTION], $QUERY[UQueryAgent::REQUEST], "Invalid query!");
    }
    $agent = $this->checkAgent($QUERY);
    $agent->setAgentName($QUERY[UQueryAgent::NAME]);
    $FACTORIES::getAgentFactory()->update($agent);
    $this->sendSuccessResponse($QUERY);
  }

  private function changeOwner($QUERY){
    global $FACTORIES;

    if(!isset($QUERY[UQueryAgent::USER])){
      $this->sendErrorResponse($QUERY[UQueryAgent::SECTION], $QUERY[UQueryAgent::REQUEST], "Invalid query!");
    }
    else if(!isset($QUERY[UQueryAgent::AGENT_ID])){
      $this->sendErrorResponse($QUERY[UQueryAgent::SECTION], $QUERY[UQueryAgent::REQUEST], "Invalid query!");
    }
    $agent = $this->checkAgent($QUERY);
    $user = null;
    if($QUERY[UQueryAgent::USER] === 0){
      $agent->setUserId(null);
      $FACTORIES::getAgentFactory()->update($agent);
      $this->sendSuccessResponse($QUERY);
    }
    else if(is_numeric($QUERY[UQueryAgent::USER])){
      $user = $FACTORIES::getUserFactory()->get($QUERY[UQueryAgent::USER]);
    }
    else{
      $qF = new QueryFilter(User::USERNAME, $QUERY[UQueryAgent::USER], "=");
      $user = $FACTORIES::getUserFactory()->filter(array($FACTORIES::FILTER => $qF), true);
    }
    if($user == null){
      $this->sendErrorResponse($QUERY[UQueryAgent::SECTION], $QUERY[UQueryAgent::REQUEST], "Invalid user specified!");
    }
    $agent->setUserId($user->getId());
    $FACTORIES::getAgentFactory()->update($agent);
    $this->sendSuccessResponse($QUERY);
  }

  private function setActive($QUERY){
    global $FACTORIES;

    if(!isset($QUERY[UQueryAgent::ACTIVE]) || !is_bool($QUERY[UQueryAgent::ACTIVE])){
      $this->sendErrorResponse($QUERY[UQueryAgent::SECTION], $QUERY[UQueryAgent::REQUEST], "Invalid query!");
    }
    $agent = $this->checkAgent($QUERY);
    $agent->setIsActive(($QUERY[UQueryAgent::ACTIVE])?1:0);
    $FACTORIES::getAgentFactory()->update($agent);
    $this->sendSuccessResponse($QUERY);
  }

  /**
   * @param array $QUERY
   * @return Agent
   */
  private function checkAgent($QUERY){
    global $FACTORIES;

    if(!isset($QUERY[UQueryAgent::AGENT_ID])){
      $this->sendErrorResponse($QUERY[UQueryAgent::SECTION], $QUERY[UQueryAgent::REQUEST], "Invalid query!");
    }
    $agent = $FACTORIES::getAgentFactory()->get($QUERY[UQueryAgent::AGENT_ID]);
    if($agent == null){
      $this->sendErrorResponse($QUERY[UQueryAgent::SECTION], $QUERY[UQueryAgent::REQUEST], "Invalid agent ID!");
    }
    else if(!AccessUtils::userCanAccessAgent($agent, $this->user)){
      $this->sendErrorResponse($QUERY[UQueryAgent::SECTION], $QUERY[UQueryAgent::REQUEST], "No access to this agent!");
    }
    return $agent;
  }

  private function listAgents($QUERY){
    global $FACTORIES;

    $accessGroups = AccessUtils::getAccessGroupsOfUser($this->user);

    $qF = new ContainFilter(AccessGroupAgent::ACCESS_GROUP_ID, Util::arrayOfIds($accessGroups));
    $accessGroupAgents = $FACTORIES::getAccessGroupAgentFactory()->filter(array($FACTORIES::FILTER => $qF));
    $agentIds = array();
    foreach ($accessGroupAgents as $accessGroupAgent) {
      $agentIds[] = $accessGroupAgent->getAgentId();
    }

    $oF = new OrderFilter(Agent::AGENT_ID, "ASC", $FACTORIES::getAgentFactory());
    $qF = new ContainFilter(Agent::AGENT_ID, $agentIds);
    $agents = $FACTORIES::getAgentFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::ORDER => $oF));
    $arr = [];
    foreach ($agents as $agent) {
      $arr[] = array(
        UResponseAgent::AGENTS_ID => $agent->getId(),
        UResponseAgent::AGENTS_NAME => $agent->getAgentName(),
        UResponseAgent::AGENTS_DEVICES => explode("\n", $agent->getDevices())
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

  private function deleteVoucher($QUERY){
    global $FACTORIES;

    if(!isset($QUERY[UQueryAgent::VOUCHER])){
      $this->sendErrorResponse($QUERY[UQueryAgent::SECTION], $QUERY[UQueryAgent::REQUEST], "Invalid delete voucher query!");
    }
    $qF = new QueryFilter(RegVoucher::VOUCHER, $QUERY['voucher'], "=");
    $voucher = $FACTORIES::getRegVoucherFactory()->filter(array($FACTORIES::FILTER => $qF), true);
    if($voucher == null){
      $this->sendErrorResponse($QUERY[UQueryAgent::SECTION], $QUERY[UQueryAgent::REQUEST], "Voucher not found!");
    }
    $FACTORIES::getRegVoucherFactory()->massDeletion(array($FACTORIES::FILTER => $qF));
    $this->sendResponse(array(
        UResponseAgent::SECTION => USection::AGENT,
        UResponseAgent::REQUEST => USectionAgent::DELETE_VOUCHER,
        UResponseAgent::RESPONSE => UValues::OK
      )
    );
  }

  private function listVouchers($QUERY){
    global $FACTORIES;

    $vouchers = $FACTORIES::getRegVoucherFactory()->filter(array());
    $arr = [];
    foreach($vouchers as $voucher){
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

  private function getBinaries($QUERY){
    global $FACTORIES;

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
    $binaries = $FACTORIES::getAgentBinaryFactory()->filter(array());
    foreach($binaries as $binary){
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

  private function createVoucher($QUERY){
    $handler = new AgentHandler();

    $voucher = Util::randomString(10);
    if(isset($QUERY[UQueryAgent::VOUCHER])){
      $voucher = $QUERY[UQueryAgent::VOUCHER];
    }
    $handler->createVoucher($voucher);
    $this->sendResponse(array(
        UResponseAgent::SECTION => USection::AGENT,
        UResponseAgent::REQUEST => USectionAgent::CREATE_VOUCHER,
        UResponseAgent::RESPONSE => UValues::OK,
        UResponseAgent::VOUCHER => $voucher
      )
    );
  }
}