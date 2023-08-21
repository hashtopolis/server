<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Routing\RouteCollectorProxy;

use DBA\Agent;
use DBA\Factory;

require_once(dirname(__FILE__) . "/shared.inc.php");


class AgentAPI extends AbstractBaseAPI {
    public static function getBaseUri(): string {
      return "/api/v2/ui/agents";
    }

    public static function getAvailableMethods(): array {
      return ['GET', 'PATCH', 'DELETE'];
    }

    public static function getDBAclass(): string {
      return Agent::class;
    }

    protected function getFactory(): object {
      return Factory::getAgentFactory();
    }

    public function getExpandables(): array {
      return ['agentstats'];
    }

    protected function getFilterACL(): array {
      return [];
    }

    public function getFormFields(): array {
    // TODO Form declarations in more generic class to allow auto-generated OpenAPI specifications
    return  [];
    }
   
    protected function createObject($mappedQuery, $QUERY): int {
      assert(False, "Chunks cannot be created via API");
      return -1;
    }

    protected function deleteObject(object $object): void {
      AgentUtils::delete($object->getId(), $this->getUser());
    }
}

AgentAPI::register($app);