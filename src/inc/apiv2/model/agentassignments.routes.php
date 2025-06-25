<?php

use DBA\AbstractModel;
use DBA\AccessGroupAgent;
use DBA\ContainFilter;
use DBA\Factory;
use DBA\Hashlist;
use DBA\JoinFilter;
use DBA\QueryFilter;
use DBA\OrderFilter;

use DBA\Agent;
use DBA\Assignment;
use DBA\Task;
use DBA\TaskWrapper;
use DBA\User;

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
  
    protected function getSingleACL(User $user, object $object): bool {
      $accessGroupsUser = Util::arrayOfIds(AccessUtils::getAccessGroupsOfUser($user));
      $agent = Factory::getAgentFactory()->get($object->getAgentId());
      $accessGroupsAgent = Util::arrayOfIds(AccessUtils::getAccessGroupsOfAgent($agent));
      
      return count(array_intersect($accessGroupsAgent, $accessGroupsUser)) > 0;
    }
    
    protected function getFilterACL(): array {
      $accessGroups = Util::arrayOfIds(AccessUtils::getAccessGroupsOfUser($this->getCurrentUser()));

      return [
        Factory::JOIN => [
          new JoinFilter(Factory::getAccessGroupAgentFactory(), Assignment::AGENT_ID, AccessGroupAgent::AGENT_ID),
          new JoinFilter(Factory::getTaskFactory(), Assignment::TASK_ID, Task::TASK_ID),
          new JoinFilter(Factory::getTaskWrapperFactory(), Task::TASK_WRAPPER_ID, TaskWrapper::TASK_WRAPPER_ID, Factory::getTaskFactory()),
          new JoinFilter(Factory::getHashlistFactory(), TaskWrapper::HASHLIST_ID, Hashlist::HASHLIST_ID, Factory::getTaskWrapperFactory()),
        ],
        Factory::FILTER => [
          new ContainFilter(AccessGroupAgent::ACCESS_GROUP_ID, $accessGroups, Factory::getAccessGroupAgentFactory()),
          new ContainFilter(Hashlist::ACCESS_GROUP_ID, $accessGroups, Factory::getHashlistFactory()),
        ]
      ];
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