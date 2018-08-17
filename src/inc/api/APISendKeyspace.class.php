<?php

use DBA\Assignment;
use DBA\QueryFilter;
use DBA\Factory;

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

    $qF1 = new QueryFilter(Assignment::AGENT_ID, $this->agent->getId(), "=");
    $qF2 = new QueryFilter(Assignment::TASK_ID, $task->getId(), "=");
    $assignment = Factory::getAssignmentFactory()->filter([Factory::FILTER => [$qF1, $qF2]], true);
    if ($assignment == null) {
      $this->sendErrorResponse(PActions::SEND_KEYSPACE, "You are not assigned to this task!");
    }

    if ($task->getKeyspace() == 0) {
      // keyspace is still required
      if($task->getIsPrince() && $keyspace == -1){
        // this is the case when the keyspace gets too large, but we still accept it
        $keyspace = DPrince::PRINCE_KEYSPACE;
      }
      else if ($keyspace < 0) {
        $this->sendErrorResponse(PActions::SEND_KEYSPACE, "Server parsed a negative keyspace, it's very likely that the number was too big to be handled by the server system!");
      }

      $task->setKeyspace($keyspace);
      Factory::getTaskFactory()->update($task);
    }

    // test if the task may have a skip value which is too high for this keyspace
    if ($task->getSkipKeyspace() > $task->getKeyspace() && $task->getKeyspace() != DPrince::PRINCE_KEYSPACE) {
      // skip is too high
      $task->setPriority(0);
      Factory::getTaskFactory()->update($task);
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