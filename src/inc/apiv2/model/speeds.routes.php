<?php

use DBA\AccessGroupAgent;
use DBA\ContainFilter;
use DBA\Factory;

use DBA\Agent;
use DBA\Hashlist;
use DBA\JoinFilter;
use DBA\Speed;
use DBA\Task;
use DBA\TaskWrapper;
use DBA\User;

require_once(dirname(__FILE__) . "/../common/AbstractModelAPI.class.php");


class SpeedAPI extends AbstractModelAPI {
    public static function getBaseUri(): string {
      return "/api/v2/ui/speeds";
    }

    public static function getAvailableMethods(): array {
      return ['GET'];
    }

    public function getPermission(): string {
      // TODO: Find proper permission
      return DAccessControl::CREATE_HASHLIST_ACCESS;
    }

    public static function getDBAclass(): string {
      return Speed::class;
    }
  
    protected function getSingleACL(User $user, object $object): bool {
      $accessGroupsUser = Util::arrayOfIds(AccessUtils::getAccessGroupsOfUser($user));
      
      $agent = Factory::getAgentFactory()->get($object->getAgentId());
      $accessGroupsAgent = Util::arrayOfIds(AccessUtils::getAccessGroupsOfAgent($agent));
      
      if (count(array_intersect($accessGroupsAgent, $accessGroupsUser)) == 0){
        return false;
      }
      
      $qF = new ContainFilter(Hashlist::ACCESS_GROUP_ID, $accessGroupsUser, Factory::getHashlistFactory());
      $jF1 = new JoinFilter(Factory::getTaskFactory(), Speed::TASK_ID, Task::TASK_ID);
      $jF2 = new JoinFilter(Factory::getTaskWrapperFactory(), Task::TASK_WRAPPER_ID, TaskWrapper::TASK_WRAPPER_ID, Factory::getTaskFactory());
      $jF3 = new JoinFilter(Factory::getHashlistFactory(), TaskWrapper::HASHLIST_ID, Hashlist::HASHLIST_ID, Factory::getTaskWrapperFactory());
      $hashlist = Factory::getSpeedFactory()->filter([Factory::FILTER => $qF, Factory::JOIN => [$jF1, $jF2, $jF3]])[Factory::getSpeedFactory()->getModelName()];
      
      return count($hashlist) > 0;
    }
  
    protected function getFilterACL(): array {
      $accessGroups = Util::arrayOfIds(AccessUtils::getAccessGroupsOfUser($this->getCurrentUser()));
      
      return [
        Factory::JOIN => [
          new JoinFilter(Factory::getAccessGroupAgentFactory(), Speed::AGENT_ID, AccessGroupAgent::AGENT_ID),
          new JoinFilter(Factory::getTaskFactory(), Speed::TASK_ID, Task::TASK_ID),
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
          'key' => Speed::AGENT_ID, 

          'relationType' => Agent::class,
          'relationKey' => Agent::AGENT_ID,
        ],
        'task' => [
          'key' => Speed::TASK_ID, 

          'relationType' => Task::class,
          'relationKey' => Task::TASK_ID,
        ],
      ];
    }

    protected function createObject(array $data): int {
      assert(False, "Speeds cannot be created via API");
      return -1;
   }

  public function updateObject(int $objectId, array $data): void {
    assert(False, "Speeds cannot be updated via API");
   }

   protected function deleteObject(object $object): void {
     assert(False, "Speeds cannot be deleted via API");
   }
}

SpeedAPI::register($app);