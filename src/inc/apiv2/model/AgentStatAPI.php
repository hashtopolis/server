<?php

namespace Hashtopolis\inc\apiv2\model;

use Exception;
use Hashtopolis\dba\AbstractModel;
use Hashtopolis\inc\utils\AccessUtils;
use Hashtopolis\dba\models\AccessGroupAgent;
use Hashtopolis\dba\ContainFilter;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\AgentStat;
use Hashtopolis\dba\ExistsFilter;
use Hashtopolis\dba\models\User;
use Hashtopolis\inc\apiv2\common\AbstractModelAPI;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\inc\Util;


/**
 * @extends AbstractModelAPI<AgentStat>
 */
class AgentStatAPI extends AbstractModelAPI {
  public static function getBaseUri(): string {
    return "/api/v2/ui/agentstats";
  }
  
  public static function getAvailableMethods(): array {
    return ['GET', 'DELETE'];
  }
  
  public static function getDBAclass(): string {
    return AgentStat::class;
  }
  
  /**
   * @param AgentStat $object
   * @throws Exception
   */
  protected function getSingleACL(User $user, AbstractModel $object): bool {
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
      Factory::FILTER => [
        new ExistsFilter(Factory::getAccessGroupAgentFactory(), AccessGroupAgent::AGENT_ID, AgentStat::AGENT_ID, [new ContainFilter(AccessGroupAgent::ACCESS_GROUP_ID, $accessGroups, Factory::getAccessGroupAgentFactory())]),
      ]
    ];
  }
  
  /**
   * @throws HttpError
   */
  protected function createObject(array $data): int {
    throw new HttpError("AgentStats cannot be created via API");
  }
  
  /**
   * @throws HttpError
   */
  public function updateObject(int $objectId, array $data): void {
    throw new HttpError("AgentStats cannot be updated via API");
  }
  
  /**
   * @param AgentStat $object
   * @throws Exception
   */
  protected function deleteObject(AbstractModel $object): void {
  }
}
