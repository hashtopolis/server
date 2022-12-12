<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Routing\RouteCollectorProxy;

use DBA\Hash;
use DBA\Factory;
use Slim\Exception\HttpException;
use Slim\Exception\HttpNotImplementedException;

require_once(dirname(__FILE__) . "/shared.inc.php");


class HashAPI extends AbstractBaseAPI {
    protected function getPermission(): string {
      // TODO: Find proper permission
      return DAccessControl::CREATE_HASHLIST_ACCESS;
    }

    protected function getFeatures(): array {
      return Hash::getFeatures();
    }

    protected function getFactory(): object {
      return Factory::getHashFactory();
    }

    protected function getExpandables(): array {
      return ["hashlist", "chunk"];
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
      /* Dummy code to implement abstract functions */
      assert(False, "Chunks cannot be created via API");
      return -1;
    }


    protected function deleteObject(object $object): void {
      /* Dummy code to implement abstract functions */
      assert(False, "Chunks cannot be deleted via API");
    }
}


$app->group("/api/v2/ui/hashes", function (RouteCollectorProxy $group) { 
    /* Allow CORS preflight requests */
    $group->options('', function (Request $request, Response $response): Response {
        return $response;
    });

    $group->get('', \HashAPI::class . ':get');
});


$app->group("/api/v2/ui/hashes/{id}", function (RouteCollectorProxy $group) {
    /* Allow preflight requests */
    $group->options('', function (Request $request, Response $response, array $args): Response {
        return $response;
    });

    $group->get('', \HashAPI::class . ':getOne');
});