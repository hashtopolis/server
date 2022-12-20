<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Routing\RouteCollectorProxy;

use DBA\CrackerBinaryType;
use DBA\Factory;
use DBA\QueryFilter;
use DBA\OrderFilter;


require_once(dirname(__FILE__) . "/shared.inc.php");


class CrackerBinaryTypeAPI extends AbstractBaseAPI {
    protected function getPermission(): string {
      // TODO: Find proper permission
      return DAccessControl::CREATE_HASHLIST_ACCESS;
    }

    protected function getFeatures(): array {
      return CrackerBinaryType::getFeatures();
    }

    protected function getFactory(): object {
      return Factory::getCrackerBinaryTypeFactory();
    }

    protected function getExpandables(): array {
      return ["crackerVersions"];
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
      CrackerUtils::createBinaryType($QUERY[CrackerBinaryType::TYPE_NAME]);

      /* On succesfully insert, return ID */
      $qFs = [
        new QueryFilter(CrackerBinaryType::TYPE_NAME, $QUERY[CrackerBinaryType::TYPE_NAME], '=')
      ];

      /* Hackish way to retreive object since Id is not returned on creation */
      $oF = new OrderFilter(CrackerBinaryType::CRACKER_BINARY_TYPE_ID, "DESC");
      $objects = $this->getFactory()->filter([Factory::FILTER => $qFs, Factory::ORDER => $oF]);
      assert(count($objects) > 1);
      
      return $objects[0]->getId();
    }


    protected function deleteObject(object $object): void {
      Factory::getCrackerBinaryTypeFactory()->delete($object);
    }
}


$app->group("/api/v2/ui/crackertypes", function (RouteCollectorProxy $group) { 
    /* Allow CORS preflight requests */
    $group->options('', function (Request $request, Response $response): Response {
        return $response;
    });

    $group->get('', \CrackerBinaryTypeAPI::class . ':get');
    $group->post('', \CrackerBinaryTypeAPI::class . ':post');
});


$app->group("/api/v2/ui/crackertypes/{id}", function (RouteCollectorProxy $group) {
    /* Allow preflight requests */
    $group->options('', function (Request $request, Response $response, array $args): Response {
        return $response;
    });

    $group->get('', \CrackerBinaryTypeAPI::class . ':getOne');
    $group->patch('', \CrackerBinaryTypeAPI::class . ':patchOne');
    $group->delete('', \CrackerBinaryTypeAPI::class . ':deleteOne');
});