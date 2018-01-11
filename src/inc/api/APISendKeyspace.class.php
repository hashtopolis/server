<?php

use DBA\Assignment;
use DBA\QueryFilter;

class APISendKeyspace extends APIBasic {
  public function execute($QUERY = array()) {
    global $FACTORIES;
    
    if (!PQuerySendKeyspace::isValid($QUERY)) {
      $this->sendErrorResponse(PActions::SEND_KEYSPACE, "Invalid keyspace query!");
    }
    $this->checkToken(PActions::SEND_KEYSPACE, $QUERY);
    $this->updateAgent(PActions::SEND_KEYSPACE);
    
    $keyspace = intval($QUERY[PQuerySendKeyspace::KEYSPACE]);
    
    $task = $FACTORIES::getTaskFactory()->get($QUERY[PQuerySendKeyspace::TASK_ID]);
    if ($task == null) {
      $this->sendErrorResponse(PActions::SEND_KEYSPACE, "Invalid task ID!");
    }
    
    $qF1 = new QueryFilter(Assignment::AGENT_ID, $this->agent->getId(), "=");
    $qF2 = new QueryFilter(Assignment::TASK_ID, $task->getId(), "=");
    $assignment = $FACTORIES::getAssignmentFactory()->filter(array($FACTORIES::FILTER => array($qF1, $qF2)), true);
    if ($assignment == null) {
      $this->sendErrorResponse(PActions::SEND_KEYSPACE, "You are not assigned to this task!");
    }
    
    if ($task->getKeyspace() == 0) {
      // keyspace is still required
      $task->setKeyspace($keyspace);
      $FACTORIES::getTaskFactory()->update($task);
    }
    
    // test if the task may have a skip value which is too high for this keyspace
    if ($task->getSkipKeyspace() > $task->getKeyspace()) {
      // skip is too high
      $task->setPriority(0);
      $FACTORIES::getTaskFactory()->update($task);
      $qF = new QueryFilter(Assignment::TASK_ID, $task->getId(), "=");
      $FACTORIES::getAssignmentFactory()->massDeletion(array($FACTORIES::FILTER => $qF));
      Util::createLogEntry(DLogEntryIssuer::API, $this->agent->getToken(), DLogEntry::ERROR, "Task with ID " . $task->getId() . " has set a skip value which is too high for its keyspace!");
    }
    
    $this->sendResponse(array(
        PResponseSendKeyspace::ACTION => PActions::SEND_KEYSPACE,
        PResponseSendKeyspace::RESPONSE => PValues::SUCCESS,
        PResponseSendKeyspace::KEYSPACE => PValues::OK
      )
    );
  }
}