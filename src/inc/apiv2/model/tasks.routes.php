<?php

use DBA\AccessGroupAgent;
use DBA\ContainFilter;
use DBA\Factory;

use DBA\Agent;
use DBA\Assignment;
use DBA\Chunk;
use DBA\CrackerBinary;
use DBA\CrackerBinaryType;
use DBA\File;
use DBA\FileTask;
use DBA\Hashlist;
use DBA\JoinFilter;
use DBA\QueryFilter;
use DBA\Speed;
use DBA\Task;
use DBA\TaskWrapper;
use DBA\User;

require_once(dirname(__FILE__) . "/../common/AbstractModelAPI.class.php");

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
      return  [
        "hashlistId" => ['type' => 'int'],
        "files" => ['type' => 'array', 'subtype' => 'int'],
      ];
    }

    protected function createObject(array $data): int {
      /* Parameter is used as primary key in database */

      $object = TaskUtils::createTask(
        $data["hashlistId"],
        $data[Task::TASK_NAME],
        $data[Task::ATTACK_CMD],
        $data[Task::CHUNK_TIME],
        $data[Task::STATUS_TIMER],
        $data[Task::USE_NEW_BENCH] ? 'speed': 'runtime',
        $data[Task::COLOR],
        $data[Task::IS_CPU_TASK],
        $data[Task::IS_SMALL],
        $data[Task::USE_PREPROCESSOR],
        $data[Task::PREPROCESSOR_COMMAND],
        $data[Task::SKIP_KEYSPACE],
        $data[Task::PRIORITY],
        $data[Task::MAX_AGENTS],
        $this->db2json($this->getFeatures()['files'], $data["files"]),
        $data[Task::CRACKER_BINARY_TYPE_ID],
        $this->getCurrentUser(),
        $data[Task::NOTES],
        $data[Task::STATIC_CHUNKS],
        $data[Task::CHUNK_SIZE]
      );
      
      return $object->getId();
    }

    //TODO make aggregate data queryable and not included by default
    static function aggregateData(object $object): array {
      $keyspace = $object->getKeyspace();
      $keyspaceProgress = $object->getKeyspaceProgress();

      $aggregatedData["dispatched"] = Util::showperc($keyspaceProgress, $keyspace);
      $aggregatedData["searched"] = Util::showperc(TaskUtils::getTaskProgress($object), $keyspace);

      $qF = new QueryFilter(Chunk::TASK_ID, $object->getId(), "=");
      $chunks = Factory::getChunkFactory()->filter([Factory::FILTER => $qF]);
      
      $activeAgents = [];
      foreach($chunks as $chunk) {
        if (time() - max($chunk->getSolveTime(), $chunk->getDispatchTime()) < SConfig::getInstance()->getVal(DConfig::CHUNK_TIMEOUT) && $chunk->getProgress() < 10000) {
          $activeAgents[$chunk->getAgentId()] = true;
        }
      }

      //status 1 is running, 2 is idle and 3 is completed
      $status = 2;
      if ($keyspaceProgress >= $keyspace && $keyspaceProgress > 0) {
        $status = 3;
      } elseif (count($activeAgents) > 0) {
        $status = 1;
      }
      
      $aggregatedData["activeAgents"] = array_keys($activeAgents);
      $aggregatedData["status"] = $status;

      return $aggregatedData;
    }

    protected function deleteObject(object $object): void {
      TaskUtils::deleteTask($object);
    }
    
    protected function getUpdateHandlers($id, $current_user): array {
      return [
        Task::IS_ARCHIVED => fn ($value) => TaskUtils::archiveTask($id, $current_user),
        Task::PRIORITY => fn ($value) => TaskUtils::updatePriority($id, $value, $current_user),
        Task::MAX_AGENTS => fn ($value) => TaskUtils::updateMaxAgents($id, $value, $current_user),
        Task::IS_CPU_TASK => fn ($value) => TaskUtils::setCpuTask($id, $value, $current_user),
        Task::CHUNK_TIME => fn ($value) => TaskUtils::changeChunkTime($id, $value, $current_user),
        Task::ATTACK_CMD => fn($value) => TaskUtils::changeAttackCmd($id, $value, $current_user),
      ];
    }
}

TaskAPI::register($app);
