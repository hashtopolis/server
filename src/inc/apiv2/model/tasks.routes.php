<?php
use DBA\Factory;
use DBA\Task;

require_once(dirname(__FILE__) . "/../common/AbstractModelAPI.class.php");

class TaskAPI extends AbstractModelAPI {
    public static function getBaseUri(): string {
      return "/api/v2/ui/tasks";
    }

    public static function getDBAclass(): string {
      return Task::class;
    }
    
    protected function getFactory(): object {
      return Factory::getTaskFactory();
    }

    public function getExpandables(): array {
      return ["assignedAgents", "crackerBinary", "crackerBinaryType", "hashlist", "speeds", "files"];
    }

    protected function getFilterACL(): array {
      return [];
    }

    public function getFormFields(): array {
    // TODO Form declarations in more generic class to allow auto-generated OpenAPI specifications
    return  [
      "hashlistId" => ['type' => 'int'],
      "files" => ['type' => 'array', 'subtype' => 'int'],
    ];
    }

    protected function createObject($mappedQuery, $QUERY): int {
      /* Parameter is used as primary key in database */

      $object = TaskUtils::createTask(
        $mappedQuery["hashlistId"],
        $mappedQuery[Task::TASK_NAME],
        $mappedQuery[Task::ATTACK_CMD],
        $mappedQuery[Task::CHUNK_TIME],
        $mappedQuery[Task::STATUS_TIMER],
        $mappedQuery[Task::USE_NEW_BENCH] ? 'speed': 'runtime',
        $mappedQuery[Task::COLOR],
        $mappedQuery[Task::IS_CPU_TASK],
        $mappedQuery[Task::IS_SMALL],
        $mappedQuery['preprocessorId'],
        $mappedQuery[Task::PREPROCESSOR_COMMAND],
        $mappedQuery[Task::SKIP_KEYSPACE],
        $mappedQuery[Task::PRIORITY],
        $mappedQuery[Task::MAX_AGENTS],
        $QUERY["files"],
        $mappedQuery[Task::CRACKER_BINARY_TYPE_ID],
        $this->getUser(),
        $mappedQuery[Task::NOTES],
        $mappedQuery[Task::STATIC_CHUNKS],
        $mappedQuery[Task::CHUNK_SIZE]
      );
      
      return $object->getId();
    }

    protected function deleteObject(object $object): void {
      TaskUtils::deleteTask($object);
    }

    public function updateObject(object $object, $data, $mappedFeatures, $processed = []): void {
      $key = Task::IS_ARCHIVED;
      if (array_key_exists($key, $data)) {
        array_push($processed, $key);
        TaskUtils::archiveTask($object->getId(), $this->getUser());
      }

      parent::updateObject($object, $data, $mappedFeatures, $processed);
    }
}

TaskAPI::register($app);