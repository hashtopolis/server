<?php

use DBA\Agent;

class APIBasic {
  /** @var Agent */
  protected $agent = null;
  
  public function execute($QUERY = array()) {
    $this->sendResponse(array(
        PResponse::ACTION => PActions::TEST_CONNECTION,
        PResponse::RESPONSE => PValues::SUCCESS
      )
    );
  }
  
  protected function sendResponse($RESPONSE) {
    header("Content-Type: application/json");
    echo json_encode($RESPONSE, true);
    die();
  }
  
  protected function updateAgent($action) {
    global $FACTORIES;
    
    $this->agent->setLastIp(Util::getIP());
    $this->agent->setLastAct($action);
    $this->agent->setLastTime(time());
    $FACTORIES->getAgentFactory()->update($this->agent);
  }
  
  public function sendErrorResponse($action, $msg) {
    $ANS = array();
    $ANS[PResponseErrorMessage::ACTION] = $action;
    $ANS[PResponseErrorMessage::RESPONSE] = PValues::ERROR;
    $ANS[PResponseErrorMessage::MESSAGE] = $msg;
    header("Content-Type: application/json");
    echo json_encode($ANS, true);
    die();
  }
  
  public function checkToken($action, $QUERY) {
    global $FACTORIES;
    
    $qF = new QueryFilter(Agent::TOKEN, $QUERY[PQuery::TOKEN], "=");
    $agent = $FACTORIES::getAgentFactory()->filter(array($FACTORIES::FILTER => array($qF)), true);
    if ($agent == null) {
      $this->sendErrorResponse($action, "Invalid token!");
    }
    $this->agent = $agent;
  }
}