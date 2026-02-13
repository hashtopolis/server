<?php

namespace Hashtopolis\inc\api;

use DLogEntry;
use DLogEntryIssuer;
use DPrince;
use Hashtopolis\inc\defines\DServerLog;
use Hashtopolis\dba\models\Assignment;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\Task;
use PActions;
use PQuerySendKeyspace;
use PResponseSendKeyspace;
use PValues;
use Hashtopolis\inc\Util;

class APISendKeyspace extends APIBasic {
  public function execute($QUERY = array()) {
    if (!PQuerySendKeyspace::isValid($QUERY)) {
      $this->sendErrorResponse(PActions::SEND_KEYSPACE, "Invalid keyspace query!");
    }
    $this->checkToken(PActions::SEND_KEYSPACE, $QUERY);
    $this->updateAgent(PActions::SEND_KEYSPACE);
    
    $keyspace = intval($QUERY[PQuerySendKeyspace::KEYSPACE]);
    
    $task = Factory::getTaskFactory()->get($QUERY[PQuerySendKeyspace::TASK_ID]);
    if ($task == null) {
      $this->sendErrorResponse(PActions::SEND_KEYSPACE, "Invalid task ID!");
    }
    
    DServerLog::log(DServerLog::TRACE, "Agent sending keyspace...", [$this->agent, $task]);
    
    $qF1 = new QueryFilter(Assignment::AGENT_ID, $this->agent->getId(), "=");
    $qF2 = new QueryFilter(Assignment::TASK_ID, $task->getId(), "=");
    $assignment = Factory::getAssignmentFactory()->filter([Factory::FILTER => [$qF1, $qF2]], true);
    if ($assignment == null) {
      DServerLog::log(DServerLog::TRACE, "Agent not assigned to task to send keyspace", [$this->agent]);
      $this->sendErrorResponse(PActions::SEND_KEYSPACE, "You are not assigned to this task!");
    }
    
    if ($task->getKeyspace() == 0) {
      // keyspace is still required
      if ($task->getUsePreprocessor() && $keyspace == -1) {
        // this is the case when the keyspace gets too large, but we still accept it
        DServerLog::log(DServerLog::TRACE, "Keyspace is too large to save, we set it to a specific number", [$this->agent]);
        $keyspace = DPrince::PRINCE_KEYSPACE;
      }
      else if ($keyspace < 0) {
        DServerLog::log(DServerLog::WARNING, "Keyspace is negative, most likely due to 32bit server", [$this->agent, $keyspace]);
        $this->sendErrorResponse(PActions::SEND_KEYSPACE, "Server parsed a negative keyspace, it's very likely that the number was too big to be handled by the server system!");
      }
      
      Factory::getTaskFactory()->set($task, Task::KEYSPACE, $keyspace);
      DServerLog::log(DServerLog::TRACE, "Keyspace saved", [$this->agent, $task]);
    }
    
    // test if the task may have a skip value which is too high for this keyspace
    if ($task->getSkipKeyspace() > $task->getKeyspace() && $task->getKeyspace() != DPrince::PRINCE_KEYSPACE) {
      // skip is too high
      DServerLog::log(DServerLog::ERROR, "Task skip value is too high, putting task inactive!", [$this->agent, $task]);
      Factory::getTaskFactory()->set($task, Task::PRIORITY, 0);
      $qF = new QueryFilter(Assignment::TASK_ID, $task->getId(), "=");
      Factory::getAssignmentFactory()->massDeletion([Factory::FILTER => $qF]);
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