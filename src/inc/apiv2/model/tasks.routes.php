<?php
use DBA\Factory;

use DBA\Agent;
use DBA\Assignment;
use DBA\CrackerBinary;
use DBA\CrackerBinaryType;
use DBA\File;
use DBA\FileTask;
use DBA\Hashlist;
use DBA\Speed;
use DBA\Task;
use DBA\TaskWrapper;

require_once(dirname(__FILE__) . "/../common/AbstractModelAPI.class.php");

class TaskAPI extends AbstractModelAPI {
    public static function getBaseUri(): string {
      return "/api/v2/ui/tasks";
    }

    public static function getDBAclass(): string {
      return Task::class;
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