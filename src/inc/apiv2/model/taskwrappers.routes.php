<?php

use DBA\AccessGroup;
use DBA\Factory;
use DBA\Hashlist;
use DBA\HashType;
use DBA\JoinFilter;
use DBA\QueryFilter;

use DBA\Task;
use DBA\TaskWrapper;

require_once(dirname(__FILE__) . "/../common/AbstractModelAPI.class.php");


class TaskWrapperAPI extends AbstractModelAPI {
    public static function getBaseUri(): string {
      return "/api/v2/ui/taskwrappers";
    }

    public static function getAvailableMethods(): array {
      return ['GET', 'PATCH', 'DELETE'];
    }

    public static function getDBAclass(): string {
      return TaskWrapper::class;
    }    

    public static function getToOneRelationships(): array {
      return [
        'accessGroup' => [
          'key' => TaskWrapper::ACCESS_GROUP_ID, 

          'relationType' => AccessGroup::class,
          'relationKey' => AccessGroup::ACCESS_GROUP_ID,
        ],
        'hashlist' => [
          'key' => TaskWrapper::HASHLIST_ID, 

          'relationType' => Hashlist::class,
          'relationKey' => Hashlist::HASHLIST_ID,
        ],
        'hashType' => [
          'key' => TaskWrapper::TASK_WRAPPER_ID,
          'parentKey' => TaskWrapper::TASK_WRAPPER_ID,
          
          'intermediateType' => Hashlist::class,
          'joinField' => TaskWrapper::HASHLIST_ID,
          'joinFieldRelation' => Hashlist::HASHLIST_ID,

          'junctionTableType' => Hashlist::class,
          'junctionTableFilterField' => Hashlist::HASH_TYPE_ID,
          'junctionTableJoinField' => Hashlist::HASHLIST_ID,

          'relationType' => HashType::class,
          'relationKey' => HashType::HASH_TYPE_ID,        
        ],
      ];
    }

    public static function getToManyRelationships(): array {
      return [
        'tasks' => [
          'key' => TaskWrapper::TASK_WRAPPER_ID,
          
          'relationType' => Task::class,
          'relationKey' => Task::TASK_WRAPPER_ID,        
        ],
      ];
    }


    protected function createObject(array $data): int {
      assert(False, "TaskWrappers cannot be created via API");
      return -1;
    }

    protected function getUpdateHandlers($id, $current_user): array {
      return [
        Taskwrapper::PRIORITY => fn ($value) => TaskwrapperUtils::updatePriority($id, $value, $current_user),
      ];
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

TaskWrapperAPI::register($app);