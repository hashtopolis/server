<?php

namespace Hashtopolis\inc\apiv2\model;

use Hashtopolis\inc\utils\AccessUtils;
use Hashtopolis\inc\defines\DAccessControl;
use Hashtopolis\dba\models\AccessGroupAgent;
use Hashtopolis\dba\ContainFilter;
use Hashtopolis\dba\Factory;

use Hashtopolis\dba\models\Agent;
use Hashtopolis\dba\models\Hashlist;
use Hashtopolis\dba\JoinFilter;
use Hashtopolis\dba\models\Speed;
use Hashtopolis\dba\models\Task;
use Hashtopolis\dba\models\TaskWrapper;
use Hashtopolis\dba\models\User;
use Hashtopolis\inc\apiv2\common\AbstractModelAPI;
use Hashtopolis\inc\apiv2\common\error\HttpError;
use Hashtopolis\inc\Util;


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
    
    if (count(array_intersect($accessGroupsAgent, $accessGroupsUser)) == 0) {
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
  
  /**
   * @throws HttpError
   */
  protected function createObject(array $data): int {
    throw new HttpError("Speeds cannot be created via API");
  }
  
  /**
   * @throws HttpError
   */
  public function updateObject(int $objectId, array $data): void {
    throw new HttpError("Speeds cannot be updated via API");
  }
  
  /**
   * @throws HttpError
   */
  protected function deleteObject(object $object): void {
    throw new HttpError("Speeds cannot be deleted via API");
  }
}
