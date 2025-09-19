<?php
use DBA\Factory;
use DBA\TaskWrapper;
use DBA\Task;
use DBA\QueryFilter;
use DBA\JoinFilter;
require_once __DIR__ . '/../apiv2/common/ErrorHandler.class.php';

class TaskwrapperUtils {

  /**
   * @param int $taskwrapperId
   * @return Taskwrapper
   * @throws HTException
   */
  public static function getTaskwrapper($taskwrapperId) {
    $taskwrapper = Factory::getTaskWrapperFactory()->get($taskwrapperId);
    if ($taskwrapper == null) {
      throw new HTException("Invalid taskwrapper!");
    }
    return $taskwrapper;
  }

  public static function updatePriority($taskWrapperId, $priority, $user) {
    $taskwrapper = TaskwrapperUtils::getTaskwrapper($taskWrapperId);

    // Priority is a bit special, when called on a 'NORMAL' running task 
    // the underlying Task object priority also gets updated
    switch ($taskwrapper->getTaskType()) {
      case DTaskTypes::NORMAL:
        $qF = new QueryFilter(TaskWrapper::TASK_WRAPPER_ID, $taskwrapper->getId(), "=", Factory::getTaskWrapperFactory());
        $jF = new JoinFilter(Factory::getTaskWrapperFactory(), Task::TASK_WRAPPER_ID, TaskWrapper::TASK_WRAPPER_ID);
        $joined = Factory::getTaskFactory()->filter([Factory::FILTER => $qF, Factory::JOIN => $jF]);
        $task = $joined[Factory::getTaskFactory()->getModelName()][0];
        if ($task === null) {
          throw new HttpError("Invallid task, Taskwrapper does not have a task");
        }
    
        TaskUtils::updatePriority($task->getId(), $priority, $user);
        break;
      case DTaskTypes::SUPERTASK:
        TaskUtils::setSupertaskPriority($taskWrapperId, $priority, $user);
        break;
      default:
        assert(False, "Internal Error: taskType not recognized");
    }
  }
}