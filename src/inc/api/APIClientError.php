<?php

namespace Hashtopolis\inc\api;

use DAgentIgnoreErrors;
use Hashtopolis\inc\DataSet;
use DConfig;
use DNotificationType;
use DPayloadKeys;
use Hashtopolis\inc\defines\DServerLog;
use Hashtopolis\dba\models\Agent;
use Hashtopolis\dba\models\AgentError;
use Hashtopolis\dba\models\Assignment;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\dba\Factory;
use Hashtopolis\inc\handlers\NotificationHandler;
use PActions;
use PQueryClientError;
use PResponseError;
use PValues;
use Hashtopolis\inc\SConfig;

class APIClientError extends APIBasic {
  public function execute($QUERY = array()) {
    //check required values
    if (!PQueryClientError::isValid($QUERY)) {
      $this->sendErrorResponse(PActions::CLIENT_ERROR, "Invalid error query!");
    }
    $this->checkToken(PActions::CLIENT_ERROR, $QUERY);
    
    // load task wrapper
    $task = Factory::getTaskFactory()->get($QUERY[PQueryClientError::TASK_ID]);
    if ($task == null) {
      DServerLog::log(DServerLog::WARNING, "Agent " . $this->agent->getId() . " tried to send error for invalid task!");
      $this->sendErrorResponse(PActions::CLIENT_ERROR, "Invalid task!");
    }
    
    //check assignment
    $qF1 = new QueryFilter(Assignment::AGENT_ID, $this->agent->getId(), "=");
    $qF2 = new QueryFilter(Assignment::TASK_ID, $task->getId(), "=");
    $assignment = Factory::getAssignmentFactory()->filter([Factory::FILTER => [$qF1, $qF2]], true);
    if ($assignment == null) {
      DServerLog::log(DServerLog::WARNING, "Agent " . $this->agent->getId() . " tried to send error for task he is not assigned to!");
      $this->sendErrorResponse(PActions::CLIENT_ERROR, "Agent is not assigned to this task!");
    }
    
    DServerLog::log(DServerLog::INFO, "Agent " . $this->agent->getId() . " sent error: " . $QUERY[PQueryClientError::MESSAGE]);
    $whitelist = explode(",", SConfig::getInstance()->getVal(DConfig::HC_ERROR_IGNORE));
    foreach ($whitelist as $w) {
      $w = trim($w);
      if (strpos($QUERY[PQueryClientError::MESSAGE], $w) !== false) {
        // error can be ignored, we just acknowledge that we received it
        $this->sendResponse(array(
            PQueryClientError::ACTION => PActions::CLIENT_ERROR,
            PResponseError::RESPONSE => PValues::SUCCESS
          )
        );
      }
    }
    
    if ($this->agent->getIgnoreErrors() <= DAgentIgnoreErrors::IGNORE_SAVE) {
      //save error message
      $chunkId = null;
      if (isset($QUERY[PQueryClientError::CHUNK_ID])) {
        $chunkId = intval($QUERY[PQueryClientError::CHUNK_ID]);
      }
      $error = new AgentError(null, $this->agent->getId(), $task->getId(), $chunkId, time(), $QUERY[PQueryClientError::MESSAGE]);
      Factory::getAgentErrorFactory()->save($error);
      
      $payload = new DataSet(array(DPayloadKeys::AGENT => $this->agent, DPayloadKeys::AGENT_ERROR => $QUERY[PQueryClientError::MESSAGE]));
      NotificationHandler::checkNotifications(DNotificationType::AGENT_ERROR, $payload);
      NotificationHandler::checkNotifications(DNotificationType::OWN_AGENT_ERROR, $payload);
    }
    
    if ($this->agent->getIgnoreErrors() == DAgentIgnoreErrors::NO) {
      //deactivate agent
      Factory::getAgentFactory()->set($this->agent, Agent::IS_ACTIVE, 0);
    }
    
    $this->updateAgent(PActions::CLIENT_ERROR);
    $this->sendResponse(array(
        PQueryClientError::ACTION => PActions::CLIENT_ERROR,
        PResponseError::RESPONSE => PValues::SUCCESS
      )
    );
  }
}