<?php

use DBA\AccessGroupAgent;
use DBA\ContainFilter;
use DBA\Hashlist;
use DBA\JoinFilter;
use DBA\Task;
use DBA\AgentError;
use DBA\Factory;
use DBA\TaskWrapper;
use DBA\User;
use JetBrains\PhpStorm\NoReturn;

require_once(dirname(__FILE__) . "/../common/AbstractModelAPI.class.php");


class AgentErrorAPI extends AbstractModelAPI {
    public static function getBaseUri(): string {
      return "/api/v2/ui/agenterrors";
    }
    /* 
    * Include the task data for the task error .
    */
    public static function getToOneRelationships(): array {
      return [
        'task' => [
          'key' => AgentError::TASK_ID,
          'relationType' => Task::class,
          'relationKey' => Task::TASK_ID,
        ],
      ];
    }
    public static function getAvailableMethods(): array {
      return ['GET', 'DELETE'];
    }

    public static function getDBAclass(): string {
      return AgentError::class;
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
          new JoinFilter(Factory::getAccessGroupAgentFactory(), AgentError::AGENT_ID, AccessGroupAgent::AGENT_ID),
          new JoinFilter(Factory::getTaskFactory(), AgentError::TASK_ID, Task::TASK_ID),
          new JoinFilter(Factory::getTaskWrapperFactory(), Task::TASK_WRAPPER_ID, TaskWrapper::TASK_WRAPPER_ID, Factory::getTaskFactory()),
          new JoinFilter(Factory::getHashlistFactory(), TaskWrapper::HASHLIST_ID, Hashlist::HASHLIST_ID, Factory::getTaskWrapperFactory()),
        ],
        Factory::FILTER => [
          new ContainFilter(AccessGroupAgent::ACCESS_GROUP_ID, $accessGroups, Factory::getAccessGroupAgentFactory()),
          new ContainFilter(Hashlist::ACCESS_GROUP_ID, $accessGroups, Factory::getHashlistFactory()),
        ]
      ];
    }
   
    #[NoReturn] protected function createObject(array $data): int {
      assert(False, "AgentErrors cannot be created via API");
    }

    #[NoReturn] public function updateObject(int $objectId, array $data): void {
      assert(False, "AgentErrors cannot be updated via API");
    }

    protected function deleteObject(object $object): void {
      Factory::getAgentErrorFactory()->delete($object);
    }
}

AgentErrorAPI::register($app);