<?php

namespace Hashtopolis\inc\apiv2\model;

use Exception;
use Hashtopolis\dba\Aggregation;
use Hashtopolis\inc\utils\AccessUtils;
use Hashtopolis\dba\ContainFilter;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\JoinFilter;
use Hashtopolis\dba\models\Assignment;
use Hashtopolis\dba\models\Chunk;
use Hashtopolis\dba\models\Hashlist;
use Hashtopolis\dba\models\Task;
use Hashtopolis\dba\models\TaskWrapper;
use Hashtopolis\dba\models\TaskWrapperDisplay;
use Hashtopolis\dba\models\User;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\inc\apiv2\common\AbstractModelAPI;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\inc\defines\DConfig;
use Hashtopolis\inc\defines\DTaskTypes;
use Hashtopolis\inc\SConfig;
use Hashtopolis\inc\Util;
use Hashtopolis\inc\utils\TaskUtils;

class TaskWrapperDisplayAPI extends AbstractModelAPI {
  public static function getBaseUri(): string {
    return "/api/v2/ui/taskwrapperdisplays";
  }
  
  public static function getAvailableMethods(): array {
    return ['GET'];
  }
  
  public function getRequiredPermissions(string $method): array {
    return [Task::PERM_READ, TaskWrapper::PERM_READ];
  }
  
  public static function getDBAclass(): string {
    return TaskWrapperDisplay::class;
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
        new JoinFilter(Factory::getHashlistFactory(), TaskWrapperDisplay::HASHLIST_ID, Hashlist::HASHLIST_ID),
      ],
      Factory::FILTER => [
        new ContainFilter(Hashlist::ACCESS_GROUP_ID, $accessGroups, Factory::getHashlistFactory()),
      ]
    ];
  }
  
  public function getAggregateFieldsets(): array {
    return [
      'taskwrapperdisplay' => [
        'totalAssignedAgents' => [$this, 'getAggregateTotalAssignedAgents'],
        'dispatched' => [$this, 'getAggregateDispatched'],
        'searched' => [$this, 'getAggregateSearched'],
        'status' => [$this, 'getAggregateStatus'],
        'currentSpeed' => [$this, 'getAggregateCurrentSpeed'],
        'estimatedTime' => [$this, 'getAggregateEstimatedTime'],
        'cprogress' => [$this, 'getAggregateCProgress'],
        'timeSpent' => [$this, 'getAggregateTimeSpent'],
      ]
    ];
  }
  
  /**
   * @throws Exception
   */
  protected function getAggregateTotalAssignedAgents(object $object): int {
    $qF = new QueryFilter(Task::TASK_WRAPPER_ID, $object->getId(), "=", Factory::getTaskFactory());
    $jF = new JoinFilter(Factory::getTaskFactory(), Assignment::TASK_ID, Task::TASK_ID);
    
    return Factory::getAssignmentFactory()->countFilter([Factory::FILTER => $qF, Factory::JOIN => $jF]);
  }
  
  /**
   * @throws HttpError
   */
  protected function getAggregateDispatched(object $object): ?string {
    if ($object->getTaskType() !== DTaskTypes::NORMAL) {
      return null;
    }
    
    $keyspace = $object->getKeyspace();
    $keyspaceProgress = $object->getKeyspaceProgress();
    return Util::showperc($keyspaceProgress, $keyspace);
  }
  
  /**
   * @throws HttpError
   * @throws Exception
   */
  protected function getAggregateSearched(object $object): ?string {
    if ($object->getTaskType() !== DTaskTypes::NORMAL) {
      return null;
    }
    
    $keyspace = $object->getKeyspace();
    $task = TaskUtils::getTasksOfWrapper($object->getId())[0];
    return Util::showperc(TaskUtils::getTaskProgress($task), $keyspace);
  }
  
  protected function getAggregateStatus(object $object): int {
    // TODO: this could be optimized by only requesting taskId, keyspace and keyspaceProgress of all tasks of that wrapper (columnFilter)
    $tasks = TaskUtils::getTasksOfWrapper($object->getId());
    $completed = 0;
    $total = 0;
    $status = 0;
    foreach ($tasks as $task) {
      $taskStatus = TaskUtils::getStatus($task);
      // if one task of the wrapper is running, it is running
      if ($taskStatus === 1) {
        $status = 1;
        break;
      }
      if ($taskStatus === 3) {
        $completed++;
      }
      $total++;
    }
    if ($status !== 1) {
      if ($total > 0 && $completed === $total) {
        $status = 3;
      }
      else {
        $status = 2;
      }
    }
    return $status;
  }
  
  /**
   * @throws HttpError
   * @throws Exception
   */
  protected function getAggregateCurrentSpeed(object $object): ?int {
    if ($object->getTaskType() !== DTaskTypes::NORMAL) {
      return null;
    }
    
    $task = TaskUtils::getTasksOfWrapper($object->getId())[0];
    
    $qF1 = new QueryFilter(Chunk::TASK_ID, $task->getId(), "=");
    $qF2 = new QueryFilter(Chunk::SOLVE_TIME, time() - SConfig::getInstance()->getVal(DConfig::CHUNK_TIMEOUT), ">");
    $qF3 = new QueryFilter(Chunk::PROGRESS, 10000, "<");
    $agg = new Aggregation(Chunk::SPEED, Aggregation::SUM);
    $speed = Factory::getChunkFactory()->multicolAggregationFilter([Factory::FILTER => [$qF1, $qF2, $qF3]], [$agg])[$agg->getName()];
    if ($speed == null) {
      $speed = 0;
    }
    return $speed;
  }
  
  /**
   * @throws HttpError
   * @throws Exception
   */
  protected function getAggregateEstimatedTime(object $object): ?int {
    if ($object->getTaskType() !== DTaskTypes::NORMAL) {
      return null;
    }
    
    $keyspace = $object->getKeyspace();
    $cProgress = $this->getAggregateCProgress($object);
    $timeSpent = $this->getAggregateTimeSpent($object);
    return ($keyspace > 0 && $cProgress > 0) ? round($timeSpent / ($cProgress / $keyspace) - $timeSpent) : 0;
  }
  
  /**
   * @throws HttpError
   * @throws Exception
   */
  protected function getAggregateCProgress(object $object): ?int {
    if ($object->getTaskType() !== DTaskTypes::NORMAL) {
      return null;
    }
    
    $task = TaskUtils::getTasksOfWrapper($object->getId())[0];
    return TaskUtils::getTaskProgress($task);
  }
  
  /**
   * @throws HttpError
   * @throws Exception
   */
  protected function getAggregateTimeSpent(object $object): ?int {
    if ($object->getTaskType() !== DTaskTypes::NORMAL) {
      return null;
    }
    
    $task = TaskUtils::getTasksOfWrapper($object->getId())[0];
    return TaskUtils::getTimeSpentOnTask($task);
  }
  
  /**
   * @throws HttpError
   */
  protected function createObject(array $data): int {
    throw new HttpError("TaskWrapperDisplays cannot be created via API");
  }
  
  /**
   * @throws HttpError
   */
  public function updateObject(int $objectId, array $data): void {
    throw new HttpError("TaskWrapperDisplays cannot be updated via API");
  }
  
  /**
   * @throws HttpError
   */
  protected function deleteObject(object $object): void {
    throw new HttpError("TaskWrapperDisplays cannot be deleted via API");
  }
  
  public static function getToManyRelationships(): array {
    return [
      'tasks' => [
        'key' => TaskWrapperDisplay::TASK_WRAPPER_ID,
        
        'relationType' => Task::class,
        'relationKey' => Task::TASK_WRAPPER_ID,
        'readonly' => true // Not allowed to change tasks of a taskwrapper 
      ],
    ];
  }
}
