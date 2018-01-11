<?php

use DBA\AgentError;
use DBA\Assignment;
use DBA\QueryFilter;

class APIClientError extends APIBasic {
  public function execute($QUERY = array()) {
    global $FACTORIES;
    
    //check required values
    if (!PQueryClientError::isValid($QUERY)) {
      $this->sendErrorResponse(PActions::CLIENT_ERROR, "Invalid error query!");
    }
    $this->checkToken(PActions::CLIENT_ERROR, $QUERY);
    
    // load task wrapper
    $task = $FACTORIES::getTaskFactory()->get($QUERY[PQueryClientError::TASK_ID]);
    if ($task == null) {
      $this->sendErrorResponse(PActions::CLIENT_ERROR, "Invalid task!");
    }
    
    //check assignment
    $qF1 = new QueryFilter(Assignment::AGENT_ID, $this->agent->getId(), "=");
    $qF2 = new QueryFilter(Assignment::TASK_ID, $task->getId(), "=");
    $assignment = $FACTORIES::getAssignmentFactory()->filter(array($FACTORIES::FILTER => array($qF1, $qF2)), true);
    if ($assignment == null) {
      $this->sendErrorResponse(PActions::CLIENT_ERROR, "Agent is not assigned to this task!");
    }
    
    //save error message
    $error = new AgentError(0, $this->agent->getId(), $task->getId(), time(), $QUERY[PQueryClientError::MESSAGE]);
    $FACTORIES::getAgentErrorFactory()->save($error);
    
    $payload = new DataSet(array(DPayloadKeys::AGENT => $this->agent, DPayloadKeys::AGENT_ERROR => $QUERY[PQueryClientError::MESSAGE]));
    NotificationHandler::checkNotifications(DNotificationType::AGENT_ERROR, $payload);
    NotificationHandler::checkNotifications(DNotificationType::OWN_AGENT_ERROR, $payload);
    
    if ($this->agent->getIgnoreErrors() == 0) {
      //deactivate agent
      $this->agent->setIsActive(0);
    }
    $this->updateAgent($QUERY);
    $this->sendResponse(array(
        PQueryClientError::ACTION => PActions::CLIENT_ERROR,
        PResponseError::RESPONSE => PValues::SUCCESS
      )
    );
  }
}