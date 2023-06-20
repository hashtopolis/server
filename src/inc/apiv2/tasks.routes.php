<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Routing\RouteCollectorProxy;

use DBA\Factory;
use DBA\Task;
use DBA\QueryFilter;
use DBA\OrderFilter;



require_once(dirname(__FILE__) . "/shared.inc.php");


class TaskAPI extends AbstractBaseAPI {
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
      return ["crackerBinary", "crackerBinaryType", "hashlist"];
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

    protected function checkPermission(object $object): bool
    {
      return true;
    }
    

    protected function createObject($QUERY): int {
      /* Parameter is used as primary key in database */

      $object = TaskUtils::createTask(
        $QUERY["hashlistId"],
        $QUERY[Task::TASK_NAME],
        $QUERY[Task::ATTACK_CMD],
        $QUERY[Task::CHUNK_TIME],
        $QUERY[Task::STATUS_TIMER],
        $QUERY[Task::USE_NEW_BENCH] ? 'speed': 'runtime',
        $QUERY[Task::COLOR],
        $QUERY[Task::IS_CPU_TASK],
        $QUERY[Task::IS_SMALL],
        $QUERY['preprocessorId'],
        $QUERY[Task::PREPROCESSOR_COMMAND],
        $QUERY[Task::SKIP_KEYSPACE],
        $QUERY[Task::PRIORITY],
        $QUERY[Task::MAX_AGENTS],
        $QUERY["files"],
        $QUERY[Task::CRACKER_BINARY_TYPE_ID],
        $this->getUser(),
        $QUERY[Task::NOTES],
        $QUERY[Task::STATIC_CHUNKS],
        $QUERY[Task::CHUNK_SIZE]
      );
      
      return $object->getId();
    }

    protected function deleteObject(object $object): void {
      TaskUtils::deleteTask($object);
    }
}

TaskAPI::register($app);