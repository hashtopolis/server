<?php
use DBA\Factory;
use DBA\QueryFilter;
use DBA\OrderFilter;

use DBA\Agent;
use DBA\Assignment;
use DBA\Task;

require_once(dirname(__FILE__) . "/../common/AbstractModelAPI.class.php");


class AgentAssignmentAPI extends AbstractModelAPI {
    public static function getBaseUri(): string {
      return "/api/v2/ui/agentassignments";
    }

    public static function getAvailableMethods(): array {
      return ['POST', 'GET', 'DELETE', 'PATCH'];
    }

    public static function getDBAclass(): string {
      return Assignment::class;
    }

    public static function getToOneRelationships(): array {
      return [
        'agent' => [
          'key' => Assignment::AGENT_ID, 

          'relationType' => Agent::class,
          'relationKey' => Agent::AGENT_ID,
        ],
        'task' => [
          'key' => Assignment::TASK_ID, 

          'relationType' => Task::class,
          'relationKey' => Task::TASK_ID,
        ],
      ];
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

    protected function getUpdateHandlers($id, $current_user): array {
      return [
        Assignment::BENCHMARK => fn ($value) => assignmentUtils::setBenchmark($id, $value, $current_user)
      ];
    }

    protected function deleteObject(object $object): void {
      AgentUtils::assign($object->getAgentId(), 0, $this->getCurrentUser());
    }
}

AgentAssignmentAPI::register($app);