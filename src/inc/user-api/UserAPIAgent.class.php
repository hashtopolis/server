<?php
use DBA\Agent;
use DBA\QueryFilter;
use DBA\RegVoucher;
use DBA\ContainFilter;
use DBA\AccessGroupAgent;
use DBA\OrderFilter;

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

        break;
      case USectionAgent::SET_ACTIVE:
        $this->setActive($QUERY);
        break;
      case USectionAgent::CHANGE_OWNER:

        break;
      case USectionAgent::SET_NAME:

        break;
      case USectionAgent::SET_CPU_ONLY:

        break;
      case USectionAgent::SET_EXTRA_PARAMS:

        break;
      case USectionAgent::SET_ERROR_FLAG:

        break;
      case USectionAgent::SET_TRUSTED:

        break;
      default:
        $this->sendErrorResponse($QUERY[UQuery::SECTION], "INV", "Invalid section request!");
    }
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