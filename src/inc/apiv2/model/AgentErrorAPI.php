<?php

namespace Hashtopolis\inc\apiv2\model;

use Exception;
use Hashtopolis\inc\utils\AccessUtils;
use Hashtopolis\dba\models\AccessGroupAgent;
use Hashtopolis\dba\ContainFilter;
use Hashtopolis\dba\models\Hashlist;
use Hashtopolis\dba\JoinFilter;
use Hashtopolis\dba\models\Task;
use Hashtopolis\dba\models\AgentError;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\TaskWrapper;
use Hashtopolis\dba\models\User;
use Hashtopolis\inc\apiv2\common\AbstractModelAPI;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\inc\Util;
use Hashtopolis\dba\ExistsFilter;


/**
 * @extends AbstractModelAPI<AgentError>
 */
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
  
  /**
   * @throws Exception
   */
  protected function getSingleACL(User $user, object $object): bool {
    $accessGroupsUser = Util::arrayOfIds(AccessUtils::getAccessGroupsOfUser($user));
    $agent = Factory::getAgentFactory()->get($object->getAgentId());
    $accessGroupsAgent = Util::arrayOfIds(AccessUtils::getAccessGroupsOfAgent($agent));
    
    return count(array_intersect($accessGroupsAgent, $accessGroupsUser)) > 0;
  }
  
  /**
   * @throws Exception
   */
  protected function getFilterACL(): array {
    $accessGroups = Util::arrayOfIds(AccessUtils::getAccessGroupsOfUser($this->getCurrentUser()));
    
    return [
      Factory::JOIN => [
        new JoinFilter(Factory::getTaskFactory(), AgentError::TASK_ID, Task::TASK_ID),
        new JoinFilter(Factory::getTaskWrapperFactory(), Task::TASK_WRAPPER_ID, TaskWrapper::TASK_WRAPPER_ID, Factory::getTaskFactory()),
        new JoinFilter(Factory::getHashlistFactory(), TaskWrapper::HASHLIST_ID, Hashlist::HASHLIST_ID, Factory::getTaskWrapperFactory()),
      ],
      Factory::FILTER => [
        new ContainFilter(Hashlist::ACCESS_GROUP_ID, $accessGroups, Factory::getHashlistFactory()),
        new ExistsFilter(Factory::getAccessGroupAgentFactory(), AccessGroupAgent::AGENT_ID, AgentError::AGENT_ID, [new ContainFilter(AccessGroupAgent::ACCESS_GROUP_ID, $accessGroups, Factory::getAccessGroupAgentFactory())]),
      ]
    ];
  }
  
  /**
   * @throws HttpError
   */
  protected function createObject(array $data): int {
    throw new HttpError("AgentErrors cannot be created via API");
  }
  
  /**
   * @throws HttpError
   */
  public function updateObject(int $objectId, array $data): void {
    throw new HttpError("AgentErrors cannot be updated via API");
  }
  
  /**
   * @throws Exception
   */
  protected function deleteObject(object $object): void {
    Factory::getAgentErrorFactory()->delete($object);
  }
}
