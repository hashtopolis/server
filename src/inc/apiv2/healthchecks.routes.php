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
    public static function getBaseUri(): string {
      return "/api/v2/ui/healthchecks";
    }
    public function getPermission(): string {
      // TODO: Find proper permission
      return DAccessControl::CREATE_HASHLIST_ACCESS;
    }

    public static function getDBAclass(): string {
      return HealthCheck::class;
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

HealthCheckAPI::register($app);