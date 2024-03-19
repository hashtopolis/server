<?php

use DBA\AccessGroup;
use DBA\Factory;
use DBA\JoinFilter;
use DBA\QueryFilter;

use DBA\Task;
use DBA\TaskWrapper;

require_once(dirname(__FILE__) . "/../common/AbstractModelAPI.class.php");


class TaskWrappersAPI extends AbstractModelAPI {
    public static function getBaseUri(): string {
      return "/api/v2/ui/taskwrappers";
    }

    public static function getAvailableMethods(): array {
      return ['GET', 'PATCH', 'DELETE'];
    }

    public static function getDBAclass(): string {
      return TaskWrapper::class;
    }    

    public function getExpandables(): array {
      return ['accessGroup', 'tasks'];
    }

    protected function fetchExpandObjects(array $objects, string $expand): mixed {     
      /* Ensure we receive the proper type */
      array_walk($objects, function($obj) { assert($obj instanceof TaskWrapper); });

      /* Expand requested section */
      switch($expand) {
        case 'accessGroup':
          return $this->getForeignKeyRelation(
            $objects,
            TaskWrapper::ACCESS_GROUP_ID,
            Factory::getAccessGroupFactory(),
            AccessGroup::ACCESS_GROUP_ID
          );
        case 'tasks':
          return $this->getManyToOneRelation(
            $objects,
            TaskWrapper::TASK_WRAPPER_ID,
            Factory::getTaskFactory(),
            Task::TASK_WRAPPER_ID
          );
        default:
          throw new BadFunctionCallException("Internal error: Expansion '$expand' not implemented!");
      }
    }

    protected function createObject(array $data): int {
      assert(False, "TaskWrappers cannot be created via API");
      return -1;
    }

    public function updateObject(object $object, array $data, array $processed = []): void {
      assert($object instanceof TaskWrapper);

      // Priority is a bit special, when called on a 'NORMAL' running task 
      // the underlying Task object priority also gets updated
      $key = TaskWrapper::PRIORITY;
      if (array_key_exists($key, $data)) {
        array_push($processed, $key);
        switch ($object->getTaskType()) {
          case DTaskTypes::NORMAL:
            $qF = new QueryFilter(TaskWrapper::TASK_WRAPPER_ID, $object->getId(), "=", Factory::getTaskWrapperFactory());
            $jF = new JoinFilter(Factory::getTaskWrapperFactory(), Task::TASK_WRAPPER_ID, TaskWrapper::TASK_WRAPPER_ID);
            $joined = Factory::getTaskFactory()->filter([Factory::FILTER => $qF, Factory::JOIN => $jF]);
            $task = $joined[Factory::getTaskFactory()->getModelName()][0];
        
            TaskUtils::updatePriority($task->getId(), $data[TaskWrapper::PRIORITY], $this->getCurrentUser());
            break;
          case DTaskTypes::SUPERTASK:
            TaskUtils::setSupertaskPriority($object->getId(), $data[TaskWrapper::PRIORITY], $this->getCurrentUser());
            break;
          default:
            assert(False, "Internal Error: taskType not recognized");
        }
      }
      parent::updateObject($object, $data, $processed);
    }
    
    protected function deleteObject(object $object): void {
      switch ($object->getTaskType()) {
        case DTaskTypes::NORMAL:
          $qF = new QueryFilter(TaskWrapper::TASK_WRAPPER_ID, $object->getId(), "=", Factory::getTaskWrapperFactory());
          $jF = new JoinFilter(Factory::getTaskWrapperFactory(), Task::TASK_WRAPPER_ID, TaskWrapper::TASK_WRAPPER_ID);
          $joined = Factory::getTaskFactory()->filter([Factory::FILTER => $qF, Factory::JOIN => $jF]);
          $task = $joined[Factory::getTaskFactory()->getModelName()][0];
          // api=true to avoid TaskUtils::delete setting 'Location:' header
          TaskUtils::delete($task->getId(), $this->getCurrentUser(), true);
          break;
        case DTaskTypes::SUPERTASK:
          TaskUtils::deleteSupertask($object->getId(), $this->getCurrentUser());
          break;
        default:
          assert(False, "Internal Error: taskType not recognized");
      }
    }
}

TaskWrappersAPI::register($app);