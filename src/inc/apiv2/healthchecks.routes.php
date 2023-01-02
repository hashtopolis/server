<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Routing\RouteCollectorProxy;

use DBA\Factory;
use DBA\HealthCheck;
use DBA\QueryFilter;
use DBA\OrderFilter;



require_once(dirname(__FILE__) . "/shared.inc.php");


class HealthCheckAPI extends AbstractBaseAPI {
    public function getPermission(): string {
      // TODO: Find proper permission
      return DAccessControl::CREATE_HASHLIST_ACCESS;
    }

    public function getFeatures(): array {
      return HealthCheck::getFeatures();
    }

    protected function getFactory(): object {
      return Factory::getHealthCheckFactory();
    }

    public function getExpandables(): array {
      return ['crackerBinary', 'healthCheckAgents'];
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
      $obj = HealthUtils::createHealthCheck(
        $QUERY['hashtypeId'],
        $QUERY['checkType'],
        $QUERY['crackerBinaryId']
      );

      return $obj->getId();
    }

    protected function deleteObject(object $object): void {
      HealthUtils::deleteHealthCheck($object->getId());
    }
}


$app->group("/api/v2/ui/healthchecks", function (RouteCollectorProxy $group) { 
    /* Allow CORS preflight requests */
    $group->options('', function (Request $request, Response $response): Response {
        return $response;
    });

    $group->get('', \HealthCheckAPI::class . ':get');
    $group->post('', \HealthCheckAPI::class . ':post');
});


$app->group("/api/v2/ui/healthchecks/{id}", function (RouteCollectorProxy $group) {
    /* Allow preflight requests */
    $group->options('', function (Request $request, Response $response, array $args): Response {
        return $response;
    });

    $group->get('', \HealthCheckAPI::class . ':getOne');
    $group->patch('', \HealthCheckAPI::class . ':patchOne');
    $group->delete('', \HealthCheckAPI::class . ':deleteOne');
});