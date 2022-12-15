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
    protected function getPermission(): string {
      // TODO: Find proper permission
      return DAccessControl::CREATE_PRETASK_ACCESS;
    }

    protected function getFeatures(): array {
      return Task::getFeatures();
    }

    protected function getFactory(): object {
      return Factory::getTaskFactory();
    }

    protected function getExpandables(): array {
      return ["crackerBinary", "crackerBinaryType"];
    }

    protected function getFilterACL(): array {
      return [];
    }

    protected function getFormFields(): array {
    // TODO Form declarations in more generic class to allow auto-generated OpenAPI specifications
    return  [];
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
        $QUERY[Task::USE_NEW_BENCH],
        $QUERY[Task::COLOR],
        $QUERY[Task::IS_CPU_TASK],
        $QUERY[Task::IS_SMALL],
        $QUERY[Task::USE_PREPROCESSOR],
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
      TaskUtils::deleteTask($object->getId());
    }
}


$app->group("/api/v2/ui/tasks", function (RouteCollectorProxy $group) { 
    /* Allow CORS preflight requests */
    $group->options('', function (Request $request, Response $response): Response {
        return $response;
    });

    $group->get('', \TaskAPI::class . ':get');
    $group->post('', \TaskAPI::class . ':post');
});


$app->group("/api/v2/ui/tasks/{id}", function (RouteCollectorProxy $group) {
    /* Allow preflight requests */
    $group->options('', function (Request $request, Response $response, array $args): Response {
        return $response;
    });

    $group->get('', \TaskAPI::class . ':getOne');
    /* FIXME: Duplicate groupNames are allowed when using patches, how-ever on creation this is checked */
    $group->patch('', \TaskAPI::class . ':patchOne');
    $group->delete('', \TaskAPI::class . ':deleteOne');
});