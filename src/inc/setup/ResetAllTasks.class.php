<?php

use DBA\ContainFilter;
use DBA\Factory;
use DBA\QueryFilter;
use DBA\Task;
use DBA\TaskWrapper;

class ResetAllTasks extends HashtopolisSetup {
  /**
   * @inheritDoc
   */
  public function execute($options) {
    if (!$this->isApplicable()) {
      return false;
    }
    $qF = new QueryFilter(TaskWrapper::IS_ARCHIVED, 0, "=");
    $taskWrappers = Factory::getTaskWrapperFactory()->filter([Factory::FILTER => $qF]);
    $qF = new ContainFilter(Task::TASK_WRAPPER_ID, Util::arrayOfIds($taskWrappers));
    $tasks = Factory::getTaskFactory()->filter([Factory::FILTER => $qF]);
    foreach ($tasks as $task) {
      try {
        TaskUtils::purgeTask($task->getId(), Login::getInstance()->getUser());
      }
      catch (HTException $e) {
        // we silently ignore it, as this happens when we iterate through tasks from other groups which this user does not see
      }
    }
    return true;
  }
  
  /**
   * @inheritDoc
   */
  public function isApplicable() {
    if ($this->isApplicableTested()) {
      return $this->getApplicableTestCache();
    }
    $qF = new QueryFilter(TaskWrapper::IS_ARCHIVED, 0, "=");
    $check = Factory::getTaskWrapperFactory()->countFilter([Factory::FILTER => $qF]);
    if ($check > 0) {
      $this->setApplicableResult(true);
      return true;
    }
    $this->setApplicableResult(false);
    return false;
  }
  
  function getIdentifier() {
    return "resetAllTasks";
  }
  
  function getSetupType() {
    return DSetupType::REMOVAL;
  }
  
  function getDescription() {
    return "Purges all tasks and cleans any data tied to the run (does not affect archived tasks).";
  }
}

HashtopolisSetup::add('ResetAllTasks', new ResetAllTasks());
