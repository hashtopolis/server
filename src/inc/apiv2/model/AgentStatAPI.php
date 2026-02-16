<?php

namespace Hashtopolis\inc\apiv2\model;

use Hashtopolis\inc\utils\AccessUtils;
use Hashtopolis\dba\models\AccessGroupAgent;
use Hashtopolis\dba\ContainFilter;
use Hashtopolis\dba\Factory;

use Hashtopolis\dba\models\AgentStat;
use Hashtopolis\dba\JoinFilter;
use Hashtopolis\dba\models\User;
use Hashtopolis\inc\apiv2\common\AbstractModelAPI;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\inc\Util;


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
        new JoinFilter(Factory::getAccessGroupAgentFactory(), AgentStat::AGENT_ID, AccessGroupAgent::AGENT_ID),
      ],
      Factory::FILTER => [
        new ContainFilter(AccessGroupAgent::ACCESS_GROUP_ID, $accessGroups, Factory::getAccessGroupAgentFactory()),
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
  
  protected function deleteObject(object $object): void {
    Factory::getAgentStatFactory()->delete($object);
  }
}
