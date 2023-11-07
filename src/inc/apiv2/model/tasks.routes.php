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

    public static function getExpandables(): array {
      return ["assignedAgents", "crackerBinary", "crackerBinaryType", "hashlist", "speeds", "files"];
    }

    protected static function fetchExpandObjects(array $objects, string $expand): mixed {     
      /* Ensure we receive the proper type */
      array_walk($objects, function($obj) { assert($obj instanceof Task); });

      /* Expand requested section */
      switch($expand) {
        case 'assignedAgents':
          return self::getManyToOneRelationViaIntermediate(
            $objects,
            Task::TASK_ID,
            Factory::getAssignmentFactory(),
            Assignment::TASK_ID,
            Factory::getAgentFactory(),
            Agent::AGENT_ID
          );
        case 'crackerBinary':
          return self::getForeignKeyRelation(
            $objects,
            Task::CRACKER_BINARY_ID,
            Factory::getCrackerBinaryFactory(),
            CrackerBinary::CRACKER_BINARY_ID
          );
        case 'crackerBinaryType':
          return self::getForeignKeyRelation(
            $objects,
            Task::CRACKER_BINARY_TYPE_ID,
            Factory::getCrackerBinaryTypeFactory(),
            CrackerBinaryType::CRACKER_BINARY_TYPE_ID
          );
        case 'hashlist':
          return self::getManyToOneRelationViaIntermediate(
            $objects,
            Task::TASK_WRAPPER_ID,
            Factory::getTaskWrapperFactory(),
            TaskWrapper::TASK_WRAPPER_ID,
            Factory::getHashlistFactory(),
            Hashlist::HASHLIST_ID
          );
        case 'speeds':
          return self::getManyToOneRelation(
            $objects,
            Task::TASK_ID,
            Factory::getSpeedFactory(),
            Speed::TASK_ID
          );
        case 'files':
          return self::getManyToOneRelationViaIntermediate(
            $objects,
            Task::TASK_ID,
            Factory::getFileTaskFactory(),
            FileTask::TASK_ID,
            Factory::getFileFactory(),
            File::FILE_ID
          );
        default:
          throw new BadFunctionCallException("Internal error: Expansion '$expand' not implemented!");
      }
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

    public function updateObject(object $object, $data,  $processed = []): void {
      $key = Task::IS_ARCHIVED;
      if (array_key_exists($key, $data)) {
        array_push($processed, $key);
        TaskUtils::archiveTask($object->getId(), $this->getCurrentUser());
      }

      /* Update connected TaskWrapper priority as well */
      $key = Task::PRIORITY;
      if (array_key_exists($key, $data)) {
        array_push($processed, $key);
        TaskUtils::updatePriority($object->getId(), $data[Task::PRIORITY], $this->getCurrentUser());
      }

      /* Update connected TaskWrapper maxAgents as well */
      $key = Task::MAX_AGENTS;
      if (array_key_exists($key, $data)) {
        array_push($processed, $key);
        TaskUtils::updateMaxAgents($object->getId(), $data[Task::MAX_AGENTS], $this->getCurrentUser());
      }

      parent::updateObject($object, $data, $processed);
    }
}

TaskAPI::register($app);