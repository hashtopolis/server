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
    
    DServerLog::log(DServerLog::TRACE, "Requesting a task...", [$this->agent]);
    
    if (HealthUtils::checkNeeded($this->agent)) {
      DServerLog::log(DServerLog::INFO, "Notified about pending health check", [$this->agent]);
      $this->sendResponse(array(
          PResponseGetTask::ACTION => PActions::GET_TASK,
          PResponseGetTask::RESPONSE => PValues::SUCCESS,
          PResponseGetTask::TASK_ID => PValuesTask::HEALTH_CHECK
        )
      );
    }
    
    if ($this->agent->getIsActive() == 0) {
      DServerLog::log(DServerLog::TRACE, "Agent is inactive and cannot get a task", [$this->agent]);
      $this->sendResponse(array(
          PResponseGetTask::ACTION => PActions::GET_TASK,
          PResponseGetTask::RESPONSE => PValues::SUCCESS,
          PResponseGetTask::TASK_ID => PValues::NONE,
          PResponseGetTask::REASON => "Agent is inactive!"
        )
      );
    }
    
    DServerLog::log(DServerLog::TRACE, "Searching for assignment and best task", [$this->agent]);
    $qF = new QueryFilter(Assignment::AGENT_ID, $this->agent->getId(), "=");
    $assignment = Factory::getAssignmentFactory()->filter([Factory::FILTER => $qF], true);
    $task = TaskUtils::getBestTask($this->agent);
    DServerLog::log(DServerLog::TRACE, "Search results", [$this->agent, $assignment, $task]);
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
          DServerLog::log(DServerLog::TRACE, "No best task available and current assigned task is fullfilled", [$this->agent]);
          $this->noTask();
        }
        if (TaskUtils::isSaturatedByOtherAgents($currentTask, $this->agent)) {
          $this->noTask();
        }
        // assignment is still good -> send this task
        DServerLog::log(DServerLog::TRACE, "Current task is running, continue with this", [$this->agent, $currentTask]);
        $this->sendTask($currentTask, $assignment);
      }
    }
    else {
      if ($assignment != null) {
        // check if this assignment has not a high enough priority to be kept
        $currentTask = Factory::getTaskFactory()->get($assignment->getTaskId());
        if ($currentTask == null) {
          // current task is not available anymore, just send the new one
          DServerLog::log(DServerLog::TRACE, "Current task does not exist anymore, send new one", [$this->agent, $task]);
          $this->sendTask($task, $assignment);
        }
        // check if this task is fulfilled, or we still have the permission on it
        $currentTask = TaskUtils::checkTask($currentTask);
        if ($currentTask == null) {
          // it got filtered out, just send new task
          DServerLog::log(DServerLog::TRACE, "Current task is done or permissions changed, send new one", [$this->agent, $task]);
          $this->sendTask($task, $assignment);
        }
        else {
          if (TaskUtils::isSaturatedByOtherAgents($currentTask, $this->agent)) {
            $this->sendTask($task, $assignment);
          }
          DServerLog::log(DServerLog::TRACE, "Current task is fine, send the more important one", [$this->agent, $currentTask, $task]);
          $this->sendTask(TaskUtils::getImportantTask($task, $currentTask), $assignment);
        }
      }
      else {
        DServerLog::log(DServerLog::TRACE, "Task available, but nothing assigned, send new one", [$this->agent, $task]);
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
      $assignment = new Assignment(null, $task->getId(), $this->agent->getId(), 0);
      $assignment = Factory::getAssignmentFactory()->save($assignment);
      DServerLog::log(DServerLog::TRACE, "No assignment present, created", [$this->agent, $assignment]);
    }
    else {
      if ($assignment->getTaskId() != $task->getId()) {
        $qF = new QueryFilter(Assignment::AGENT_ID, $this->agent->getId(), "=");
        Factory::getAssignmentFactory()->massDeletion([Factory::FILTER => $qF]);
        DServerLog::log(DServerLog::TRACE, "Current task does not match assignment, delete it", [$this->agent, $assignment]);
        $assignment = new Assignment(null, $task->getId(), $this->agent->getId(), 0);
        $assignment = Factory::getAssignmentFactory()->save($assignment);
        DServerLog::log(DServerLog::TRACE, "Created new assignment", [$this->agent, $assignment]);
      }
    }
    
    $taskWrapper = Factory::getTaskWrapperFactory()->get($task->getTaskWrapperId());
    if ($taskWrapper == null) {
      DServerLog::log(DServerLog::FATAL, "Inconsistency between taskWrapper and task", [$this->agent, $task]);
      $this->sendErrorResponse(PActions::GET_TASK, "Inconsistent TaskWrapper information!");
    }
    $hashlist = Factory::getHashlistFactory()->get($taskWrapper->getHashlistId());
    if ($hashlist == null) {
      DServerLog::log(DServerLog::TRACE, "Inconsistency between taskWrapper and hashlist", [$this->agent, $taskWrapper]);
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
    
    $hashtype = Factory::getHashTypeFactory()->get($hashlist->getHashTypeId());
    
    DServerLog::log(DServerLog::TRACE, "Sending task to agent", [$this->agent, $task, $taskFiles]);
    
    $brain = ($hashlist->getBrainId() && !$task->getForcePipe() && !$task->getUsePreprocessor()) ? true : false;
    
    $response = array(
      PResponseGetTask::ACTION => PActions::GET_TASK,
      PResponseGetTask::RESPONSE => PValues::SUCCESS,
      PResponseGetTask::TASK_ID => (int)$task->getId(),
      PResponseGetTask::ATTACK_COMMAND => $task->getAttackCmd(),
      PResponseGetTask::CMD_PARAMETERS => " --hash-type=" . $hashlist->getHashTypeId() . " " . $this->agent->getCmdPars(),
      PResponseGetTask::HASHLIST_ID => (int)$taskWrapper->getHashlistId(),
      PResponseGetTask::BENCHMARK => (int)SConfig::getInstance()->getVal(DConfig::BENCHMARK_TIME),
      PResponseGetTask::STATUS_TIMER => (int)$task->getStatusTimer(),
      PResponseGetTask::FILES => $taskFiles,
      PResponseGetTask::CRACKER_ID => $task->getCrackerBinaryId(),
      PResponseGetTask::BENCHTYPE => ($task->getUseNewBench() == 1) ? "speed" : "run",
      PResponseGetTask::HASHLIST_ALIAS => SConfig::getInstance()->getVal(DConfig::HASHLIST_ALIAS),
      PResponseGetTask::KEYSPACE => $task->getKeyspace(),
      PResponseGetTask::USE_PREPROCESSOR => ($task->getUsePreprocessor()) ? true : false,
      PResponseGetTask::PREPROCESSOR => $task->getUsePreprocessor(),
      PResponseGetTask::PREPROCESSOR_COMMAND => $task->getPreprocessorCommand(),
      PResponseGetTask::ENFORCE_PIPE => ($task->getForcePipe()) ? true : false,
      PResponseGetTask::SLOW_HASH => ($hashtype->getIsSlowHash()) ? true : false,
      PResponseGetTask::USE_BRAIN => $brain,
    );
    
    if ($brain) {
      $response[PResponseGetTask::BRAIN_HOST] = SConfig::getInstance()->getVal(DConfig::HASHCAT_BRAIN_HOST);
      $response[PResponseGetTask::BRAIN_PORT] = intval(SConfig::getInstance()->getVal(DConfig::HASHCAT_BRAIN_PORT));
      $response[PResponseGetTask::BRAIN_PASS] = SConfig::getInstance()->getVal(DConfig::HASHCAT_BRAIN_PASS);
      $response[PResponseGetTask::BRAIN_FEATURES] = (int)$hashlist->getBrainFeatures();
    }
    
    $this->sendResponse($response);
  }
}