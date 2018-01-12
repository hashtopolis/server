<?php

use DBA\AccessGroupAgent;
use DBA\Agent;
use DBA\Assignment;
use DBA\Chunk;
use DBA\ContainFilter;
use DBA\File;
use DBA\FileTask;
use DBA\Hashlist;
use DBA\JoinFilter;
use DBA\OrderFilter;
use DBA\QueryFilter;
use DBA\Task;
use DBA\TaskWrapper;

abstract class APIBasic {
  /** @var Agent */
  protected $agent = null;
  
  /**
   * @param array $QUERY input query sent to the API
   */
  public abstract function execute($QUERY = array());
  
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





















