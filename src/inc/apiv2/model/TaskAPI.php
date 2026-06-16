<?php

namespace Hashtopolis\inc\apiv2\model;

use Exception;
use Hashtopolis\dba\OrderFilter;
use Hashtopolis\inc\defines\DConfig;
use Hashtopolis\inc\defines\DPrince;
use Hashtopolis\inc\utils\AccessUtils;
use Hashtopolis\dba\ContainFilter;
use Hashtopolis\dba\Factory;

use Hashtopolis\dba\models\Agent;
use Hashtopolis\dba\Aggregation;
use Hashtopolis\dba\models\Assignment;
use Hashtopolis\dba\models\Chunk;
use Hashtopolis\dba\models\CrackerBinary;
use Hashtopolis\dba\models\CrackerBinaryType;
use Hashtopolis\dba\models\File;
use Hashtopolis\dba\models\FileTask;
use Hashtopolis\dba\models\Hashlist;
use Hashtopolis\dba\JoinFilter;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\dba\models\Speed;
use Hashtopolis\dba\models\Task;
use Hashtopolis\dba\models\TaskWrapper;
use Hashtopolis\dba\models\User;
use Hashtopolis\inc\apiv2\common\AbstractModelAPI;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\inc\SConfig;
use Hashtopolis\inc\utils\TaskUtils;
use Hashtopolis\inc\Util;

class TaskAPI extends AbstractModelAPI {
  public static function getBaseUri(): string {
    return "/api/v2/ui/tasks";
  }
  
  public static function getDBAclass(): string {
    return Task::class;
  }
  
  protected function getSingleACL(User $user, object $object): bool {
    $accessGroupsUser = Util::arrayOfIds(AccessUtils::getAccessGroupsOfUser($user));
    
    $qF1 = new ContainFilter(Hashlist::ACCESS_GROUP_ID, $accessGroupsUser, Factory::getHashlistFactory());
    $qF2 = new QueryFilter(Task::TASK_ID, $object->getId(), "=");
    $jF1 = new JoinFilter(Factory::getTaskWrapperFactory(), Task::TASK_WRAPPER_ID, TaskWrapper::TASK_WRAPPER_ID, Factory::getTaskFactory());
    $jF2 = new JoinFilter(Factory::getHashlistFactory(), TaskWrapper::HASHLIST_ID, Hashlist::HASHLIST_ID, Factory::getTaskWrapperFactory());
    $tasks = Factory::getTaskFactory()->filter([Factory::FILTER => [$qF1, $qF2], Factory::JOIN => [$jF1, $jF2]])[Factory::getTaskFactory()->getModelName()];
    
    return count($tasks) > 0;
  }
  
  protected function getFilterACL(): array {
    $accessGroups = Util::arrayOfIds(AccessUtils::getAccessGroupsOfUser($this->getCurrentUser()));
    
    return [
      Factory::JOIN => [
        new JoinFilter(Factory::getTaskWrapperFactory(), Task::TASK_WRAPPER_ID, TaskWrapper::TASK_WRAPPER_ID),
        new JoinFilter(Factory::getHashlistFactory(), TaskWrapper::HASHLIST_ID, Hashlist::HASHLIST_ID, Factory::getTaskWrapperFactory()),
      ],
      Factory::FILTER => [
        new ContainFilter(Hashlist::ACCESS_GROUP_ID, $accessGroups, Factory::getHashlistFactory()),
      ]
    ];
  }
  
  public static function getToOneRelationships(): array {
    return [
      'crackerBinary' => [
        'key' => Task::CRACKER_BINARY_ID,
        
        'relationType' => CrackerBinary::class,
        'relationKey' => CrackerBinary::CRACKER_BINARY_ID,
      ],
      'crackerBinaryType' => [
        'key' => Task::CRACKER_BINARY_TYPE_ID,
        
        'relationType' => CrackerBinaryType::class,
        'relationKey' => CrackerBinaryType::CRACKER_BINARY_TYPE_ID,
      ],
      'hashlist' => [
        'key' => TaskWrapper::HASHLIST_ID,
        
        'relationType' => Hashlist::class,
        'relationKey' => Hashlist::HASHLIST_ID,
        
        //because task doesnt have a direct connection to hashlist
        'intermediateType' => TaskWrapper::class,
        'joinField' => Task::TASK_WRAPPER_ID,
        'joinFieldRelation' => TaskWrapper::TASK_WRAPPER_ID,
        
        'junctionTableType' => TaskWrapper::class,
        'junctionTableFilterField' => TaskWrapper::HASHLIST_ID,
        'junctionTableJoinField' => TaskWrapper::TASK_WRAPPER_ID,
        
        'parentKey' => Task::TASK_ID
      ],
    ];
  }
  
  public static function getToManyRelationships(): array {
    return [
      'assignedAgents' => [
        'key' => Task::TASK_ID,
        
        'junctionTableType' => Assignment::class,
        'junctionTableFilterField' => Assignment::TASK_ID,
        'junctionTableJoinField' => Assignment::AGENT_ID,
        
        'relationType' => Agent::class,
        'relationKey' => Agent::AGENT_ID,
      ],
      'files' => [
        'key' => Task::TASK_ID,
        
        'junctionTableType' => FileTask::class,
        'junctionTableFilterField' => FileTask::TASK_ID,
        'junctionTableJoinField' => FileTask::FILE_ID,
        
        'relationType' => File::class,
        'relationKey' => File::FILE_ID,
      ],
      'speeds' => [
        'key' => Task::TASK_ID,
        
        'relationType' => Speed::class,
        'relationKey' => Speed::TASK_ID,
      ]
    ];
  }
  
  public function getFormFields(): array {
    // TODO Form declarations in more generic class to allow auto-generated OpenAPI specifications
    return [
      "hashlistId" => ['type' => 'int'],
      "files" => ['type' => 'array', 'subtype' => 'int'],
    ];
  }
  
  public function getAggregateFieldsets(): array {
    return [
      'task' => [
        'totalAssignedAgents',
        'dispatched',
        'searched',
        'status',
        'totalNumberOfChunks',
        'currentSpeed',
        'estimatedTime',
        'cprogress',
        'timeSpent'
      ]
    ];
  }
  
  /**
   * @throws HttpError
   */
  protected function createObject(array $data): int {
    /* Parameter is used as primary key in database */
    
    $task = TaskUtils::createTask(
      $data["hashlistId"],
      $data[Task::TASK_NAME],
      $data[Task::ATTACK_CMD],
      $data[Task::CHUNK_TIME],
      $data[Task::STATUS_TIMER],
      $data[Task::USE_NEW_BENCH] ? 'speed' : 'runtime',
      $data[Task::COLOR],
      $data[Task::IS_CPU_TASK],
      $data[Task::IS_SMALL],
      $data[Task::USE_PREPROCESSOR],
      $data[Task::PREPROCESSOR_COMMAND],
      $data[Task::SKIP_KEYSPACE],
      $data[Task::PRIORITY],
      $data[Task::MAX_AGENTS],
      $this->db2json($this->getFeatures()['files'], $data["files"]),
      $data[Task::CRACKER_BINARY_ID],
      $this->getCurrentUser(),
      $data[Task::NOTES],
      $data[Task::STATIC_CHUNKS],
      $data[Task::CHUNK_SIZE],
      $data[Task::FORCE_PIPE]
    );
    
    return $task->getId();
  }
  
  /**
   * @param object $object
   * @param array $includedData
   * @param array|null $aggregateFieldsets
   * @return array
   * @throws Exception
   */
  function aggregateData(object $object, array &$includedData = [], ?array $aggregateFieldsets = null): array {
    /** @var $object Task */
    $aggregatedData = [];
    
    if (!is_null($aggregateFieldsets) && array_key_exists('task', $aggregateFieldsets)) {
      $aggregateFieldsets['task'] = explode(",", $aggregateFieldsets['task']);
      
      if (in_array("assignedAgents", $aggregateFieldsets['task'])) {
        $qF = new QueryFilter(Assignment::TASK_ID, $object->getId(), "=");
        $assignedAgents = Factory::getAssignmentFactory()->countFilter([Factory::FILTER => $qF]);
        $aggregatedData["totalAssignedAgents"] = $assignedAgents;
      }
      
      $keyspace = $object->getKeyspace();
      $keyspaceProgress = $object->getKeyspaceProgress();
      
      if (in_array("dispatched", $aggregateFieldsets['task'])) {
        $aggregatedData["dispatched"] = Util::showperc($keyspaceProgress, $keyspace);
      }
      
      if (in_array("searched", $aggregateFieldsets['task'])) {
        $aggregatedData["searched"] = Util::showperc(TaskUtils::getTaskProgress($object), $keyspace);
      }
      
      if (in_array("status", $aggregateFieldsets['task'])) {
        // the filter for progress is needed so we reduce the checked chunks numbers by a lot
        $qF1 = new QueryFilter(Chunk::TASK_ID, $object->getId(), "=");
        $qF2 = new QueryFilter(Chunk::PROGRESS, 10000, "<");
        $chunks = Factory::getChunkFactory()->filter([Factory::FILTER => [$qF1, $qF2]]);
        $aggregatedData["status"] = TaskUtils::getStatus($chunks, $keyspace, $keyspaceProgress);
      }
      
      if (in_array("totalNumberOfChunks", $aggregateFieldsets['task'])) {
        $qF = new QueryFilter(Chunk::TASK_ID, $object->getId(), "=");
        $aggregatedData["totalNumberOfChunks"] = Factory::getChunkFactory()->countFilter([Factory::FILTER => $qF]);
      }
      
      if (in_array("currentSpeed", $aggregateFieldsets['task'])) {
        $qF1 = new QueryFilter(Chunk::TASK_ID, $object->getId(), "=");
        $qF2 = new QueryFilter(Chunk::SOLVE_TIME, time() - SConfig::getInstance()->getVal(DConfig::CHUNK_TIMEOUT), ">");
        $qF3 = new QueryFilter(Chunk::PROGRESS, 10000, "<");
        $agg = new Aggregation(Chunk::SPEED, Aggregation::SUM);
        $speed = Factory::getChunkFactory()->multicolAggregationFilter([Factory::FILTER => [$qF1, $qF2, $qF3]], [$agg])[$agg->getName()];
        if ($speed == null) {
          $speed = 0;
        }
        $aggregatedData["currentSpeed"] = $speed;
      }
      
      if (in_array("estimatedTime", $aggregateFieldsets['task']) ||
        in_array("timeSpent", $aggregateFieldsets['task']) ||
        in_array("cprogress", $aggregateFieldsets['task'])) {
        
        $cProgress = TaskUtils::getTaskProgress($object);
        if (in_array("cprogress", $aggregateFieldsets['task'])) {
          $aggregatedData["cprogress"] = $cProgress;
        }
        
        $timeSpent = TaskUtils::getTimeSpentOnTask($object);
        
        if (in_array("timeSpent", $aggregateFieldsets['task'])) {
          $aggregatedData["timeSpent"] = $timeSpent;
        }
        
        if (in_array("estimatedTime", $aggregateFieldsets['task'])) {
          $aggregatedData["estimatedTime"] = ($keyspace > 0 && $cProgress > 0) ? round($timeSpent / ($cProgress / $keyspace) - $timeSpent) : 0;
        }
      }
    }
    
    return $aggregatedData;
  }
  
  protected function deleteObject(object $object): void {
    /** @var $object Task */
    TaskUtils::deleteTask($object);
  }
  
  protected function getUpdateHandlers($id, $current_user): array {
    return [
      Task::IS_ARCHIVED => fn($value) => TaskUtils::toggleArchiveTask($id, $value, $current_user),
      Task::PRIORITY => fn($value) => TaskUtils::updatePriority($id, $value, $current_user),
      Task::MAX_AGENTS => fn($value) => TaskUtils::updateMaxAgents($id, $value, $current_user),
      Task::IS_CPU_TASK => fn($value) => TaskUtils::setCpuTask($id, $value, $current_user),
      Task::CHUNK_TIME => fn($value) => TaskUtils::changeChunkTime($id, $value, $current_user),
      Task::ATTACK_CMD => fn($value) => TaskUtils::changeAttackCmd($id, $value, $current_user),
    ];
  }
}

