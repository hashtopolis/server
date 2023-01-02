<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Routing\RouteCollectorProxy;

use DBA\PreTask;
use DBA\Factory;
use DBA\QueryFilter;
use DBA\OrderFilter;

require_once(dirname(__FILE__) . "/shared.inc.php");


class PreTaskAPI extends AbstractBaseAPI {
    public function getPermission(): string {
      // TODO: Find proper permission
      return DAccessControl::CREATE_HASHLIST_ACCESS;
    }

    public function getFeatures(): array {
      return PreTask::getFeatures();
    }

    protected function getFactory(): object {
      return Factory::getPretaskFactory();
    }

    public function getExpandables(): array {
      return ["pretaskFiles"];
    }

    protected function getFilterACL(): array {
      return [];
    }

    public function getFormFields(): array {
    // TODO Form declarations in more generic class to allow auto-generated OpenAPI specifications
      return [
        "files" => ['type' => 'array', 'subtype' => 'int']
      ];
    }

    protected function checkPermission(object $object): bool
    {
      return true;
    }
    

    protected function createObject($QUERY): int {
      PretaskUtils::createPretask(
        $QUERY[PreTask::TASK_NAME],
        $QUERY[PreTask::ATTACK_CMD],
        $QUERY[PreTask::CHUNK_TIME],
        $QUERY[PreTask::STATUS_TIMER],
        $QUERY[PreTask::COLOR],
        $QUERY[PreTask::IS_CPU_TASK],
        $QUERY[PreTask::IS_SMALL],
        $QUERY[PreTask::USE_NEW_BENCH],
        $QUERY["files"],
        $QUERY[PreTask::CRACKER_BINARY_TYPE_ID],
        $QUERY[PreTask::MAX_AGENTS],
        $QUERY[PreTask::PRIORITY]
      );

      /* On succesfully insert, return ID */
      $qFs = [
        new QueryFilter(PreTask::TASK_NAME, $QUERY[PreTask::TASK_NAME], '='),
        new QueryFilter(PreTask::ATTACK_CMD, $QUERY[PreTask::ATTACK_CMD], '=')
      ];

      /* Hackish way to retreive object since Id is not returned on creation */
      $oF = new OrderFilter(PreTask::PRETASK_ID, "DESC");
      $objects = $this->getFactory()->filter([Factory::FILTER => $qFs, Factory::ORDER => $oF]);
      assert(count($objects) == 1);

      return $objects[0]->getId();      
    }

    protected function deleteObject(object $object): void {
      $this->getFactory()->delete($object);
    }
}


$app->group("/api/v2/ui/pretasks", function (RouteCollectorProxy $group) { 
    /* Allow CORS preflight requests */
    $group->options('', function (Request $request, Response $response): Response {
        return $response;
    });

    $group->get('', \PreTaskAPI::class . ':get');
    $group->post('', \PreTaskAPI::class . ':post');
});


$app->group("/api/v2/ui/pretasks/{id}", function (RouteCollectorProxy $group) {
    /* Allow preflight requests */
    $group->options('', function (Request $request, Response $response, array $args): Response {
        return $response;
    });

    $group->get('', \PreTaskAPI::class . ':getOne');
    $group->patch('', \PreTaskAPI::class . ':patchOne');
    $group->delete('', \PreTaskAPI::class . ':deleteOne');
});