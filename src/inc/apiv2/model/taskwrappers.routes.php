<?php
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
      return ['GET'];
    }

    public static function getDBAclass(): string {
      return TaskWrapper::class;
    }    

    protected function getFactory(): object {
      return Factory::getTaskWrapperFactory();
    }

    public function getExpandables(): array {
      return ['accessGroup', 'tasks'];
    }

    protected function doExpand(object $object, string $expand): mixed {
      assert($object instanceof TaskWrapper);
      switch($expand) {
        case 'accessGroup':
          $obj = Factory::getAccessGroupFactory()->get($object->getAccessGroupId());
          return $this->obj2Array($obj);
        case 'tasks':
          $qF = new QueryFilter(TaskWrapper::TASK_WRAPPER_ID, $object->getId(), "=", Factory::getTaskWrapperFactory());
          $jF = new JoinFilter(Factory::getTaskWrapperFactory(), Task::TASK_WRAPPER_ID, TaskWrapper::TASK_WRAPPER_ID);
          return $this->joinQuery(Factory::getTaskFactory(), $qF, $jF);    
      }
    }

    protected function createObject(array $data): int {
      assert(False, "TaskWrappers cannot be created via API");
      return -1;
    }

    public function updateObject(object $object, array $data, array $processed = []): void {
      assert(False, "TaskWrappers cannot be updated via API");
    }
    
    protected function deleteObject(object $object): void {
      assert(False, "TaskWrappers cannot be deleted via API");
    }
}

TaskWrappersAPI::register($app);