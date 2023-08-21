<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Routing\RouteCollectorProxy;

use DBA\Assignment;
use DBA\Factory;
use DBA\QueryFilter;
use DBA\OrderFilter;
use PhpParser\Node\Expr\Assign;

require_once(dirname(__FILE__) . "/shared.inc.php");


class AgentAssignmentAPI extends AbstractBaseAPI {
    public static function getBaseUri(): string {
      return "/api/v2/ui/agentassignments";
    }

    public static function getAvailableMethods(): array {
      return ['POST', 'GET', 'DELETE'];
    }

    public static function getDBAclass(): string {
      return Assignment::class;
    }

    protected function getFactory(): object {
      return Factory::getAssignmentFactory();
    }

    public function getExpandables(): array {
      return ["task", "agent"];
    }

    protected function getFilterACL(): array {
      return [];
    }

    public function getFormFields(): array {
      return  [];
    }

    protected function createObject($QUERY): int {
      AgentUtils::assign($QUERY[Assignment::AGENT_ID], $QUERY[Assignment::TASK_ID], $this->getUser());
      /* On succesfully insert, return ID */
      $qFs = [
        new QueryFilter(Assignment::AGENT_ID, $QUERY[Assignment::AGENT_ID], '='),
        new QueryFilter(Assignment::TASK_ID, $QUERY[Assignment::TASK_ID], '=')
      ];

      /* Hackish way to retreive object since Id is not returned on creation */
      $oF = new OrderFilter(Assignment::ASSIGNMENT_ID, "DESC");
      $objects = $this->getFactory()->filter([Factory::FILTER => $qFs, Factory::ORDER => $oF]);
      assert(count($objects) >= 1);

      return $objects[0]->getId();      
    }

    public function updateObject(object $object, array $data, array $mappedFeatures, array $processed = []): void {
      assert(False, "AgentAssignments cannot be updated via API");
    }

    protected function deleteObject(object $object): void {
      AgentUtils::assign($object->getAgentId(), 0, $this->getUser());
    }
}

AgentAssignmentAPI::register($app);