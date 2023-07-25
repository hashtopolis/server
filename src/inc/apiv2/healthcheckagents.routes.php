<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Routing\RouteCollectorProxy;

use DBA\Factory;
use DBA\HealthCheckAgent;
use DBA\QueryFilter;
use DBA\OrderFilter;



require_once(dirname(__FILE__) . "/shared.inc.php");


class HealthCheckAgentAPI extends AbstractBaseAPI {
    public static function getBaseUri(): string {
      return "/api/v2/ui/healthcheckagents";
    }

    public static function getAvailableMethods(): array {
      return ['GET'];
    }

    public function getPermission(): string {
      // TODO: Find proper permission
      return DAccessControl::CREATE_HASHLIST_ACCESS;
    }

    public static function getDBAclass(): string {
      return HealthCheckAgent::class;
    }
    protected function getFactory(): object {
      return Factory::getHealthCheckAgentFactory();
    }

    public function getExpandables(): array {
      return ['agent', 'healthCheck'];
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
       assert(False, "HealthCheckAgents cannot be created via API");
       return -1;
    }

    protected function deleteObject(object $object): void {
      /* Dummy code to implement abstract functions */
      assert(False, "HealthCheckAgents cannot be deleted via API");
    }
}

HealthCheckAgentAPI::register($app);