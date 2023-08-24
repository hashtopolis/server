<?php
use DBA\Factory;
use DBA\JoinFilter;
use DBA\QueryFilter;

use DBA\Agent;
use DBA\Assignment;
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
    
    protected function getFactory(): object {
      return Factory::getTaskFactory();
    }

    public function getExpandables(): array {
      return ["assignedAgents", "crackerBinary", "crackerBinaryType", "hashlist", "speeds", "files"];
    }

    protected function doExpand(object $object, string $expand): mixed {
      assert($object instanceof Task);
      switch($expand) {
        case 'assignedAgents':
          $qF = new QueryFilter(Assignment::TASK_ID, $object->getId(), "=", Factory::getAssignmentFactory());
          $jF = new JoinFilter(Factory::getAssignmentFactory(), Agent::AGENT_ID, Assignment::AGENT_ID);
          return $this->joinQuery(Factory::getAgentFactory(), $qF, $jF);
        case 'crackerBinary':
          $obj = Factory::getCrackerBinaryFactory()->get($object->getCrackerBinaryId());
          return $this->obj2Array($obj);
        case 'crackerBinaryType':
          $obj = Factory::getCrackerBinaryTypeFactory()->get($object->getCrackerBinaryTypeId());
          return $this->obj2Array($obj);
        case 'hashlist':
          // Tasks are bit of a special case, as in the task the hashlist is not directly available.
          // To get this information we need to join the task with the Hashlist and the TaskWrapper to get the Hashlist.
          $qF = new QueryFilter(TaskWrapper::TASK_WRAPPER_ID, $object->getTaskWrapperId(), "=", Factory::getTaskWrapperFactory());
          $jF = new JoinFilter(Factory::getTaskWrapperFactory(), Hashlist::HASHLIST_ID, TaskWrapper::HASHLIST_ID);
          return $this->joinQuery(Factory::getHashlistFactory(), $qF, $jF);
        case 'speeds':
          $qF = new QueryFilter(Speed::TASK_ID, $object->getId(), "=");
          return $this->filterQuery(Factory::getSpeedFactory(), $qF);
        case 'files':
          $qF = new QueryFilter(FileTask::TASK_ID, $object->getId(), "=", Factory::getFileTaskFactory());
          $jF = new JoinFilter(Factory::getFileTaskFactory(), File::FILE_ID, FileTask::FILE_ID);
          return $this->joinQuery(Factory::getFileFactory(), $qF, $jF);
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
        $data["files"],
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

      parent::updateObject($object, $data, $processed);
    }
}

TaskAPI::register($app);