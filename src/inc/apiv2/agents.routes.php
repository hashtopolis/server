<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Routing\RouteCollectorProxy;

use DBA\Agent;
use DBA\Factory;

require_once(dirname(__FILE__) . "/shared.inc.php");


class AgentAPI extends AbstractBaseAPI {
    public function getPermission(): string {
      // TODO: Find proper permission
      return DAccessControl::CREATE_HASHLIST_ACCESS;
    }

    public function getFeatures(): array {
      return Agent::getFeatures();
    }

    protected function getFactory(): object {
      return Factory::getAgentFactory();
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
      assert(False, "Chunks cannot be created via API");
      return -1;
    }

    protected function deleteObject(object $object): void {
      Factory::getAgentFactory()->delete($object);
    }
}


$app->group("/api/v2/ui/agents", function (RouteCollectorProxy $group) { 
    /* Allow CORS preflight requests */
    $group->options('', function (Request $request, Response $response): Response {
        return $response;
    });

    $group->get('', \AgentAPI::class . ':get');
    //$group->post('', \AgentAPI::class . ':post');
});


$app->group("/api/v2/ui/agents/{id}", function (RouteCollectorProxy $group) {
    /* Allow preflight requests */
    $group->options('', function (Request $request, Response $response, array $args): Response {
        return $response;
    });

    $group->get('', \AgentAPI::class . ':getOne');
    $group->patch('', \AgentAPI::class . ':patchOne');
    $group->delete('', \AgentAPI::class . ':deleteOne');
});