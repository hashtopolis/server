<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Routing\RouteCollectorProxy;

use DBA\Factory;
use DBA\AccessGroup;
use DBA\QueryFilter;
use DBA\OrderFilter;



require_once(dirname(__FILE__) . "/shared.inc.php");


class AccessGroupAPI extends AbstractBaseAPI {
    public function getPermission(): string {
      // TODO: Find proper permission
      return DAccessControl::CREATE_HASHLIST_ACCESS;
    }

    public function getFeatures(): array {
      return AccessGroup::getFeatures();
    }

    protected function getFactory(): object {
      return Factory::getAccessGroupFactory();
    }

    public function getExpandables(): array {
      return ["userMembers", "agentMembers"];
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

      $object = AccessGroupUtils::createGroup($QUERY[AccessGroup::GROUP_NAME]);
      return $object->getId();
    }

    protected function deleteObject(object $object): void {
      AccessGroupUtils::deleteGroup($object->getId());
    }
}


$app->group("/api/v2/ui/accessgroups", function (RouteCollectorProxy $group) { 
    /* Allow CORS preflight requests */
    $group->options('', function (Request $request, Response $response): Response {
        return $response;
    });

    $group->get('', \AccessGroupAPI::class . ':get');
    $group->post('', \AccessGroupAPI::class . ':post');
});


$app->group("/api/v2/ui/accessgroups/{id}", function (RouteCollectorProxy $group) {
    /* Allow preflight requests */
    $group->options('', function (Request $request, Response $response, array $args): Response {
        return $response;
    });

    $group->get('', \AccessGroupAPI::class . ':getOne');
    /* FIXME: Duplicate groupNames are allowed when using patches, how-ever on creation this is checked */
    $group->patch('', \AccessGroupAPI::class . ':patchOne');
    $group->delete('', \AccessGroupAPI::class . ':deleteOne');
});