<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Routing\RouteCollectorProxy;

use DBA\AgentStat;
use DBA\Factory;

require_once(dirname(__FILE__) . "/shared.inc.php");


class AgentStatsAPI extends AbstractBaseAPI {
    public static function getBaseUri(): string {
      return "/api/v2/ui/agentstats";
    }

    public static function getAvailableMethods(): array {
      return ['GET', 'DELETE'];
    }

    public function getPermission(): string {
      // TODO: Find proper permission
      return DAccessControl::CREATE_HASHLIST_ACCESS;
    }

    public static function getDBAclass(): string {
      return AgentStat::class;
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
      assert(False, "AgentStats cannot be created via API");
      return -1;
    }

    protected function deleteObject(object $object): void {
      Factory::getAgentStatFactory()->delete($object);
    }
}

AgentStatsAPI::register($app);