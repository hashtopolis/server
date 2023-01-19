<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Routing\RouteCollectorProxy;

use DBA\AgentStat;
use DBA\Factory;

require_once(dirname(__FILE__) . "/shared.inc.php");


class AgentStatsAPI extends AbstractBaseAPI {
    public function getPermission(): string {
      // TODO: Find proper permission
      return DAccessControl::CREATE_HASHLIST_ACCESS;
    }

    public function getFeatures(): array {
      return AgentStat::getFeatures();
    }

    protected function getFactory(): object {
      return Factory::getAgentStatFactory();
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
      Factory::getAgentStatFactory()->delete($object);
    }
}


$app->group("/api/v2/ui/agentstats", function (RouteCollectorProxy $group) { 
    /* Allow CORS preflight requests */
    $group->options('', function (Request $request, Response $response): Response {
        return $response;
    });

    $group->get('', \AgentStatsAPI::class . ':get');
});


$app->group("/api/v2/ui/agentstats/{id}", function (RouteCollectorProxy $group) {
    /* Allow preflight requests */
    $group->options('', function (Request $request, Response $response, array $args): Response {
        return $response;
    });

    $group->get('', \AgentStatsAPI::class . ':getOne');
    $group->delete('', \AgentStatsAPI::class . ':deleteOne');
});