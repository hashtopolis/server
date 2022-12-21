<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Routing\RouteCollectorProxy;

use DBA\Supertask;
use DBA\Factory;
use DBA\QueryFilter;
use DBA\OrderFilter;

require_once(dirname(__FILE__) . "/shared.inc.php");


class SupertaskAPI extends AbstractBaseAPI {
    protected function getPermission(): string {
      // TODO: Find proper permission
      return DAccessControl::CREATE_HASHLIST_ACCESS;
    }

    protected function getFeatures(): array {
      return Supertask::getFeatures();
    }

    protected function getFactory(): object {
      return Factory::getSupertaskFactory();
    }

    protected function getExpandables(): array {
      return [ "pretasks" ];
    }

    protected function getFilterACL(): array {
      return [];
    }

    protected function getFormFields(): array {
    // TODO Form declarations in more generic class to allow auto-generated OpenAPI specifications
      return  [
        "pretasks" => ['type' => 'array', 'subtype' => 'int']
      ];
    }

    protected function checkPermission(object $object): bool
    {
      return true;
    }
    

    protected function createObject($QUERY): int {
      SupertaskUtils::createSupertask(
        $QUERY[Supertask::SUPERTASK_NAME],
        $QUERY["pretasks"],
      );

      /* On succesfully insert, return ID */
      $qFs = [
        new QueryFilter(Supertask::SUPERTASK_NAME, $QUERY[Supertask::SUPERTASK_NAME], '=')
      ];

      /* Hackish way to retreive object since Id is not returned on creation */
      $oF = new OrderFilter(Supertask::SUPERTASK_ID, "DESC");
      $objects = $this->getFactory()->filter([Factory::FILTER => $qFs, Factory::ORDER => $oF]);
      /* No unique properties set on columns, thus multiple entries could exists, pick the latest (DESC ordering used) */
      assert(count($objects) > 1);

      return $objects[0]->getId();      
    }

    protected function deleteObject(object $object): void {
      $this->getFactory()->delete($object);
    }
}


$app->group("/api/v2/ui/supertasks", function (RouteCollectorProxy $group) { 
    /* Allow CORS preflight requests */
    $group->options('', function (Request $request, Response $response): Response {
        return $response;
    });

    $group->get('', \SupertaskAPI::class . ':get');
    $group->post('', \SupertaskAPI::class . ':post');
});


$app->group("/api/v2/ui/supertasks/{id}", function (RouteCollectorProxy $group) {
    /* Allow preflight requests */
    $group->options('', function (Request $request, Response $response, array $args): Response {
        return $response;
    });

    $group->get('', \SupertaskAPI::class . ':getOne');
    $group->patch('', \SupertaskAPI::class . ':patchOne');
    $group->delete('', \SupertaskAPI::class . ':deleteOne');
});