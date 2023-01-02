<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Routing\RouteCollectorProxy;

use DBA\HashType;
use DBA\Factory;

require_once(dirname(__FILE__) . "/shared.inc.php");


class HashTypeAPI extends AbstractBaseAPI {
    public function getPermission(): string {
      // TODO: Find proper permission
      return DAccessControl::CREATE_HASHLIST_ACCESS;
    }

    public function getFeatures(): array {
      return HashType::getFeatures();
    }

    protected function getFactory(): object {
      return Factory::getHashTypeFactory();
    }

    public function getExpandables(): array {
      return [];
    }

    protected function getFilterACL(): array {
      return [];
    }

    public function getFormFields(): array {
    // TODO Form declarations in more generic class to allow auto-generated OpenAPI specifications
    return  [];
    }

    protected function checkPermission(object $object): bool
    {
      return true;
    }
    

    protected function createObject($QUERY): int {
      /* Parameter is used as primary key in database */
      $hashtypeId = $QUERY[HashType::HASH_TYPE_ID];

      HashtypeUtils::addHashtype(
        $hashtypeId,
        $QUERY[HashType::DESCRIPTION],
        $QUERY[HashType::IS_SALTED],
        $QUERY[HashType::IS_SLOW_HASH],
        $this->getUser()
      );

      /* On succesfully insert, return ID */
      return $hashtypeId;
    }

    protected function deleteObject(object $object): void {
      HashtypeUtils::deleteHashtype($object->getId());
    }
}


$app->group("/api/v2/ui/hashtypes", function (RouteCollectorProxy $group) { 
    /* Allow CORS preflight requests */
    $group->options('', function (Request $request, Response $response): Response {
        return $response;
    });

    $group->get('', \HashTypeAPI::class . ':get');
    $group->post('', \HashTypeAPI::class . ':post');
});


$app->group("/api/v2/ui/hashtypes/{id}", function (RouteCollectorProxy $group) {
    /* Allow preflight requests */
    $group->options('', function (Request $request, Response $response, array $args): Response {
        return $response;
    });

    $group->get('', \HashTypeAPI::class . ':getOne');
    $group->patch('', \HashTypeAPI::class . ':patchOne');
    $group->delete('', \HashTypeAPI::class . ':deleteOne');
});