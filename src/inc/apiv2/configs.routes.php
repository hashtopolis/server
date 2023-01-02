<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Routing\RouteCollectorProxy;

use DBA\Factory;
use DBA\Config;
use DBA\QueryFilter;
use DBA\OrderFilter;



require_once(dirname(__FILE__) . "/shared.inc.php");


class ConfigAPI extends AbstractBaseAPI {
    public function getPermission(): string {
      // TODO: Find proper permission
      return DAccessControl::CREATE_HASHLIST_ACCESS;
    }

    public function getFeatures(): array {
      return Config::getFeatures();
    }

    protected function getFactory(): object {
      return Factory::getConfigFactory();
    }

    public function getExpandables(): array {
      return ['configSection'];
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
       /* Dummy code to implement abstract functions */
       assert(False, "Configs cannot be created via API");
       return -1;
    }

    protected function deleteObject(object $object): void {
      /* Dummy code to implement abstract functions */
      assert(False, "Configs cannot be deleted via API");
    }
}


$app->group("/api/v2/ui/configs", function (RouteCollectorProxy $group) { 
    /* Allow CORS preflight requests */
    $group->options('', function (Request $request, Response $response): Response {
        return $response;
    });

    $group->get('', \ConfigAPI::class . ':get');
});


$app->group("/api/v2/ui/configs/{id}", function (RouteCollectorProxy $group) {
    /* Allow preflight requests */
    $group->options('', function (Request $request, Response $response, array $args): Response {
        return $response;
    });

    $group->get('', \ConfigAPI::class . ':getOne');
    $group->patch('', \ConfigAPI::class . ':patchOne');
});