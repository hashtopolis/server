<?php

use DBA\Assignment;
use DBA\File;
use DBA\FileTask;
use DBA\JoinFilter;
use DBA\QueryFilter;
use DBA\Task;
use DBA\Factory;

class APIGetTask extends APIBasic {
  public function execute($QUERY = array()) {
    if (!PQueryGetTask::isValid($QUERY)) {
      $this->sendErrorResponse(PActions::GET_TASK, "Invalid task query!");
    }
    $this->checkToken(PActions::GET_TASK, $QUERY);
    $this->updateAgent(PActions::GET_TASK);

    if ($this->agent->getIsActive() == 0) {
      $this->sendResponse(array(
          PResponseGetTask::ACTION => PActions::GET_TASK,
          PResponseGetTask::RESPONSE => PValues::SUCCESS,
          PResponseGetTask::TASK_ID => PValues::NONE,
          PResponseGetTask::REASON => "Agent is inactive!"
        )
      );
    }

    $qF = new QueryFilter(Assignment::AGENT_ID, $this->agent->getId(), "=");
    $assignment = Factory::getAssignmentFactory()->filter([Factory::FILTER => $qF], true);
    $task = TaskUtils::getBestTask($this->agent);
    if ($task == null) {
      if ($assignment == null) {
        // there is no best task available and nothing is assigned currently -> no task to assign
        $this->noTask();
      }
      else {
        // check if the current assignment is fulfilled
        $currentTask = Factory::getTaskFactory()->get($assignment->getTaskId());
        $currentTask = TaskUtils::checkTask($currentTask);
        if ($currentTask == null) {
          // we checked the task and it is completed
          $this->noTask();
        }
        // assignment is still good -> send this task
        $this->sendTask($currentTask, $assignment);
      }
    }
    else {
      if ($assignment != null) {
        // check if this assignment has not a high enough priority to be kept
        $currentTask = Factory::getTaskFactory()->get($assignment->getTaskId());
        if ($currentTask == null) {
          // current task is not available anymore, just send the new one
          $this->sendTask($task, $assignment);
        }
        // check if this task is fulfilled, or we still have the permission on it
        $currentTask = TaskUtils::checkTask($currentTask);
        if ($currentTask == null) {
          // it got filtered out, just send new task
          $this->sendTask($task, $assignment);
        }
        else {
          $this->sendTask(TaskUtils::getImportantTask($task, $currentTask), $assignment);
        }
      }
      else {
        $this->sendTask($task, $assignment);
      }
    }
  }

  private function noTask() {
    $this->sendResponse(array(
        PResponseGetTask::ACTION => PActions::GET_TASK,
        PResponseGetTask::RESPONSE => PValues::SUCCESS,
        PResponseGetTask::TASK_ID => PValues::NONE,
        PResponseGetTask::REASON => "No suitable task available!"
      )
    );
  }

  /**
   * @param $task Task
   * @param $assignment Assignment
   */
  private function sendTask($task, $assignment) {
    // check if the assignment is up-to-date and correct if needed
    if ($assignment == null) {
      $assignment = new Assignment(0, $task->getId(), $this->agent->getId(), 0);
      Factory::getAssignmentFactory()->save($assignment);
    }
    else {
      if ($assignment->getTaskId() != $task->getId()) {
        $qF = new QueryFilter(Assignment::AGENT_ID, $this->agent->getId(), "=");
        Factory::getAssignmentFactory()->massDeletion([Factory::FILTER => $qF]);
        $assignment = new Assignment(0, $task->getId(), $this->agent->getId(), 0);
        Factory::getAssignmentFactory()->save($assignment);
      }
    }

    $taskWrapper = Factory::getTaskWrapperFactory()->get($task->getTaskWrapperId());
    if ($taskWrapper == null) {
      $this->sendErrorResponse(PActions::GET_TASK, "Inconsistent TaskWrapper information!");
    }
    $hashlist = Factory::getHashlistFactory()->get($taskWrapper->getHashlistId());
    if ($hashlist == null) {
      $this->sendErrorResponse(PActions::GET_TASK, "Inconsistent TaskWrapper-Hashlist information");
    }

    $taskFiles = array();
    $qF = new QueryFilter(FileTask::TASK_ID, $task->getId(), "=", Factory::getFileTaskFactory());
    $jF = new JoinFilter(Factory::getFileTaskFactory(), File::FILE_ID, FileTask::FILE_ID);
    $joined = Factory::getFileFactory()->filter([Factory::FILTER => $qF, Factory::JOIN => $jF]);
    /** @var $files File[] */
    $files = $joined[Factory::getFileFactory()->getModelName()];
    foreach ($files as $file) {
      $taskFiles[] = $file->getFilename();
    }

    $this->sendResponse(array(
        PResponseGetTask::ACTION => PActions::GET_TASK,
        PResponseGetTask::RESPONSE => PValues::SUCCESS,
        PResponseGetTask::TASK_ID => (int)$task->getId(),
        PResponseGetTask::ATTACK_COMMAND => $task->getAttackCmd(),
        PResponseGetTask::CMD_PARAMETERS => " -p " . SConfig::getInstance()->getVal(DConfig::FIELD_SEPARATOR) . " --hash-type=" . $hashlist->getHashTypeId() . " " . $this->agent->getCmdPars(),
        PResponseGetTask::HASHLIST_ID => (int)$taskWrapper->getHashlistId(),
        PResponseGetTask::BENCHMARK => (int)SConfig::getInstance()->getVal(DConfig::BENCHMARK_TIME),
        PResponseGetTask::STATUS_TIMER => (int)$task->getStatusTimer(),
        PResponseGetTask::FILES => $taskFiles,
        PResponseGetTask::CRACKER_ID => $task->getCrackerBinaryId(),
        PResponseGetTask::BENCHTYPE => ($task->getUseNewBench() == 1) ? "speed" : "run", // TODO: this need to be adapted also for generic
        PResponseGetTask::HASHLIST_ALIAS => SConfig::getInstance()->getVal(DConfig::HASHLIST_ALIAS),
        PResponseGetTask::KEYSPACE => $task->getKeyspace(),
        PResponseGetTask::PRINCE => ($task->getIsPrince()) ? true : false
      )
    );
  }
}