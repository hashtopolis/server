<?php
use DBA\Factory;
use DBA\QueryFilter;
use DBA\OrderFilter;

use DBA\Assignment;

require_once(dirname(__FILE__) . "/../common/AbstractModelAPI.class.php");


class AgentAssignmentAPI extends AbstractModelAPI {
    public static function getBaseUri(): string {
      return "/api/v2/ui/agentassignments";
    }

    public static function getAvailableMethods(): array {
      return ['POST', 'GET', 'DELETE'];
    }

    public static function getDBAclass(): string {
      return Assignment::class;
    }

    public function getExpandables(): array {
      return ["task", "agent"];
    }

    protected function doExpand(object $object, string $expand): mixed {
      assert($object instanceof Assignment);

      switch($expand) {
        case 'task':
          $obj = Factory::getTaskFactory()->get($object->getTaskId());
          return $this->obj2Array($obj);
        case 'agent':
          $obj = Factory::getAgentFactory()->get($object->getAgentId());
          return $this->obj2Array($obj);
      }
    }  

    protected function createObject(array $data): int {
      AgentUtils::assign($data[Assignment::AGENT_ID], $data[Assignment::TASK_ID], $this->getCurrentUser());
      /* On succesfully insert, return ID */
      $qFs = [
        new QueryFilter(Assignment::AGENT_ID, $data[Assignment::AGENT_ID], '='),
        new QueryFilter(Assignment::TASK_ID, $data[Assignment::TASK_ID], '=')
      ];

      /* Hackish way to retreive object since Id is not returned on creation */
      $oF = new OrderFilter(Assignment::ASSIGNMENT_ID, "DESC");
      $objects = $this->getFactory()->filter([Factory::FILTER => $qFs, Factory::ORDER => $oF]);
      assert(count($objects) >= 1);

      return $objects[0]->getId();      
    }

    public function updateObject(object $object, array $data, array $processed = []): void {
      assert(False, "AgentAssignments cannot be updated via API");
    }

    protected function deleteObject(object $object): void {
      AgentUtils::assign($object->getAgentId(), 0, $this->getCurrentUser());
    }
}

AgentAssignmentAPI::register($app);