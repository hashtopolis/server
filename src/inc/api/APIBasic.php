<?php

namespace Hashtopolis\inc\api;

use Hashtopolis\inc\agent\PResponseErrorMessage;
use Hashtopolis\inc\agent\PValues;
use Hashtopolis\inc\defines\DServerLog;
use Hashtopolis\dba\models\Agent;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\dba\Factory;
use Hashtopolis\inc\HTException;
use Hashtopolis\inc\agent\PQuery;
use Hashtopolis\inc\Util;

abstract class APIBasic {
  /** @var Agent */
  protected $agent = null;
  
  /**
   * @param array $QUERY input query sent to the API
   * @throws HTException
   */
  public abstract function execute($QUERY = array());
  
  protected function sendResponse($RESPONSE) {
    header("Content-Type: application/json");
    echo json_encode($RESPONSE);
    die();
  }
  
  protected function updateAgent($action): void {
    Factory::getAgentFactory()->mset($this->agent, [Agent::LAST_IP => Util::getIP(), Agent::LAST_ACT => $action, Agent::LAST_TIME => time()]);
  }
  
  public function sendErrorResponse($action, $msg): void {
    $ANS = array();
    $ANS[PResponseErrorMessage::ACTION] = $action;
    $ANS[PResponseErrorMessage::RESPONSE] = PValues::ERROR;
    $ANS[PResponseErrorMessage::MESSAGE] = $msg;
    header("Content-Type: application/json");
    echo json_encode($ANS);
    die();
  }
  
  public function checkToken($action, $QUERY): void {
    $qF = new QueryFilter(Agent::TOKEN, $QUERY[PQuery::TOKEN], "=");
    $agent = Factory::getAgentFactory()->filter([Factory::FILTER => array($qF)], true);
    if ($agent == null) {
      DServerLog::log(DServerLog::WARNING, "Agent from " . Util::getIP() . " sent invalid token!");
      $this->sendErrorResponse($action, "Invalid token!");
    }
    $this->agent = $agent;
  }
}
