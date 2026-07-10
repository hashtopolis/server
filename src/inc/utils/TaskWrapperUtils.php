<?php

namespace Hashtopolis\inc\utils;

use Exception;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\TaskWrapper;
use Hashtopolis\dba\models\Task;
use Hashtopolis\dba\models\User;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\dba\JoinFilter;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\inc\defines\DTaskTypes;
use Hashtopolis\inc\HTException;

class TaskWrapperUtils {
  
  /**
   * @param int $taskWrapperId
   * @return Taskwrapper
   * @throws HTException
   * @throws Exception
   */
  public static function getTaskWrapper(int $taskWrapperId): TaskWrapper {
    $taskWrapper = Factory::getTaskWrapperFactory()->get($taskWrapperId);
    if ($taskWrapper == null) {
      throw new HTException("Invalid taskwrapper!");
    }
    return $taskWrapper;
  }
  
  /**
   * @param int $taskWrapperId
   * @param int $priority
   * @param User $user
   * @return void
   * @throws HTException
   * @throws HttpError
   * @throws Exception
   */
  public static function updatePriority(int $taskWrapperId, int $priority, User $user): void {
    $taskWrapper = TaskWrapperUtils::getTaskWrapper($taskWrapperId);
    
    // Priority is a bit special, when called on a 'NORMAL' running task 
    // the underlying Task object priority also gets updated
    switch ($taskWrapper->getTaskType()) {
      case DTaskTypes::NORMAL:
        $qF = new QueryFilter(TaskWrapper::TASK_WRAPPER_ID, $taskWrapper->getId(), "=", Factory::getTaskWrapperFactory());
        $jF = new JoinFilter(Factory::getTaskWrapperFactory(), Task::TASK_WRAPPER_ID, TaskWrapper::TASK_WRAPPER_ID);
        $joined = Factory::getTaskFactory()->filter([Factory::FILTER => $qF, Factory::JOIN => $jF]);
        $task = $joined[Factory::getTaskFactory()->getModelName()][0];
        if ($task === null) {
          throw new HttpError("Invalid task, Taskwrapper does not have a task");
        }
        
        TaskUtils::updatePriority($task->getId(), $priority, $user);
        break;
      case DTaskTypes::SUPERTASK:
        TaskUtils::setSupertaskPriority($taskWrapperId, $priority, $user);
        break;
      default:
        throw new HttpError("Internal Error: taskType not recognized");
    }
  }
}
