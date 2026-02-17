<?php

namespace Hashtopolis\inc\apiv2\model;

use Hashtopolis\inc\defines\DTaskTypes;
use Hashtopolis\inc\utils\AccessUtils;
use Hashtopolis\dba\models\AccessGroup;
use Hashtopolis\dba\CoalesceOrderFilter;
use Hashtopolis\dba\ContainFilter;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\Hashlist;
use Hashtopolis\dba\models\HashType;
use Hashtopolis\dba\JoinFilter;
use Hashtopolis\dba\QueryFilter;

use Hashtopolis\dba\models\Task;
use Hashtopolis\dba\models\TaskWrapper;
use Hashtopolis\dba\models\User;
use Hashtopolis\inc\apiv2\common\AbstractModelAPI;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\inc\HTException;
use Hashtopolis\inc\utils\TaskUtils;
use Hashtopolis\inc\Util;
use Hashtopolis\inc\utils\TaskWrapperUtils;


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
  
  protected function getSingleACL(User $user, object $object): bool {
    $accessGroupsUser = Util::arrayOfIds(AccessUtils::getAccessGroupsOfUser($user));
    
    $qF1 = new ContainFilter(Hashlist::ACCESS_GROUP_ID, $accessGroupsUser, Factory::getHashlistFactory());
    $qF2 = new QueryFilter(TaskWrapper::TASK_WRAPPER_ID, $object->getId(), "=");
    $jF = new JoinFilter(Factory::getHashlistFactory(), TaskWrapper::HASHLIST_ID, Hashlist::HASHLIST_ID);
    $wrappers = Factory::getTaskWrapperFactory()->filter([Factory::FILTER => [$qF1, $qF2], Factory::JOIN => $jF])[Factory::getTaskWrapperFactory()->getModelName()];
    
    return count($wrappers) > 0;
  }
  
  protected function getFilterACL(): array {
    $accessGroups = Util::arrayOfIds(AccessUtils::getAccessGroupsOfUser($this->getCurrentUser()));
    
    return [
      Factory::JOIN => [
        new JoinFilter(Factory::getHashlistFactory(), TaskWrapper::HASHLIST_ID, Hashlist::HASHLIST_ID),
      ],
      Factory::FILTER => [
        new ContainFilter(Hashlist::ACCESS_GROUP_ID, $accessGroups, Factory::getHashlistFactory()),
      ]
    ];
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
      'task' => [
        'key' => TaskWrapper::TASK_WRAPPER_ID,
        
        'relationType' => Task::class,
        'relationKey' => Task::TASK_WRAPPER_ID,
        'readonly' => true // Not allowed to change tasks of a taskwrapper 
      ],
    ];
  }
  
  public static function getToManyRelationships(): array {
    return [
      'tasks' => [
        'key' => TaskWrapper::TASK_WRAPPER_ID,
        
        'relationType' => Task::class,
        'relationKey' => Task::TASK_WRAPPER_ID,
        'readonly' => true // Not allowed to change tasks of a taskwrapper 
      ],
    ];
  }
  
  protected function parseFilters(array $filters): array {
    //This is in order to handle filters and sorting on columns
    if (isset($filters[Factory::JOIN])) {
      $joinFilters = $filters[Factory::JOIN];
      foreach ($joinFilters as $joinFilter) {
        if ($joinFilter->getOtherTableName() == Task::class) {
          // This is a leftjoin where the task type is 0 which means not a supertask. This is in order to 
          // create a to 1 relationship where the taskwrapper will have the normal task as a relation and a supertask will have null
          // This way it becomes possible to filter or sort on the included single task.
          $joinFilter->setJoinType(JoinFilter::LEFT);
          $qf = new QueryFilter(TaskWrapper::TASK_TYPE, DTaskTypes::NORMAL, "=");
          $joinFilter->setQueryFilters([$qf]);
        }
      }
      
      // parse the order and filter
      // Because the frontend shows taskwrappername for supertasks and taskname for normaltasks, the orders and filters for the 
      // name needs to be changed to coalesce filters to get the correct value between these 2. 
      // Another possibility where this hack is not needed would be to also store the taskname of normal tasks in the
      // taskwrapper
      if (isset($filters[Factory::ORDER])) {
        foreach ($filters[Factory::ORDER] as &$orderfilter) {
          if ($orderfilter->getBy() == Task::TASK_NAME) {
            $concatColumns = [new ConcatColumn(TaskWrapper::TASK_WRAPPER_NAME, Factory::getTaskWrapperFactory()), new ConcatColumn(Task::TASK_NAME, Factory::getTaskFactory())];
            $newOrderFilter = new ConcatOrderFilter($concatColumns, $orderfilter->getType());
            $orderfilter = $newOrderFilter;
          }
        }
        unset($orderfilter);
      }

      if (isset($filters[Factory::FILTER])) {
        foreach($filters[Factory::FILTER] as &$filter) {
          if ($filter instanceof LikeFilterInsensitive && $filter->getKey() == Task::TASK_NAME) {
            $concatColumns = [new ConcatColumn(TaskWrapper::TASK_WRAPPER_NAME, Factory::getTaskWrapperFactory()), new ConcatColumn(Task::TASK_NAME, Factory::getTaskFactory())];
            $newFilter = new ConcatLikeFilterInsensitive($concatColumns, $filter->getValue());
            $filter = $newFilter;
          }
        }
        unset($filter);
      }
    }
    return $filters;
  }
  
  /**
   * @throws HttpError
   */
  protected function createObject(array $data): int {
    throw new HttpError("TaskWrappers cannot be created via API");
  }
  
  protected function getUpdateHandlers($id, $current_user): array {
    return [
      Taskwrapper::PRIORITY => fn($value) => TaskwrapperUtils::updatePriority($id, $value, $current_user),
    ];
  }
  
  /**
   * @throws HTException
   * @throws HttpError
   */
  protected function deleteObject(object $object): void {
    switch ($object->getTaskType()) {
      case DTaskTypes::NORMAL:
        $qF = new QueryFilter(TaskWrapper::TASK_WRAPPER_ID, $object->getId(), "=", Factory::getTaskWrapperFactory());
        $jF = new JoinFilter(Factory::getTaskWrapperFactory(), Task::TASK_WRAPPER_ID, TaskWrapper::TASK_WRAPPER_ID);
        $joined = Factory::getTaskFactory()->filter([Factory::FILTER => $qF, Factory::JOIN => $jF]);
        $task = $joined[Factory::getTaskFactory()->getModelName()][0];
        // api=true to avoid TaskUtils::delete setting 'Location:' header
        if ($task !== null) {
          TaskUtils::delete($task->getId(), $this->getCurrentUser(), true);
        }
        else {
          // This should not happen because every taskwrapper should have a task
          // but since there are no database constraints this cant be enforced.
          Factory::getTaskWrapperFactory()->delete($object);
        }
        break;
      case DTaskTypes::SUPERTASK:
        TaskUtils::deleteSupertask($object->getId(), $this->getCurrentUser());
        break;
      default:
        throw new HttpError("Internal Error: taskType not recognized");
    }
  }
}
