<?php

namespace Hashtopolis\inc\apiv2\model;

use Hashtopolis\inc\utils\AccessUtils;
use Hashtopolis\dba\models\AccessGroupAgent;
use Hashtopolis\dba\ContainFilter;
use Hashtopolis\dba\Factory;

use Hashtopolis\dba\models\Agent;
use Hashtopolis\dba\models\HealthCheck;
use Hashtopolis\dba\models\HealthCheckAgent;
use Hashtopolis\dba\JoinFilter;
use Hashtopolis\dba\models\User;
use Hashtopolis\inc\apiv2\common\AbstractModelAPI;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\inc\Util;


class HealthCheckAgentAPI extends AbstractModelAPI {
  public static function getBaseUri(): string {
    return "/api/v2/ui/healthcheckagents";
  }
  
  public static function getAvailableMethods(): array {
    return ['GET'];
  }
  
  public static function getDBAclass(): string {
    return HealthCheckAgent::class;
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
        new JoinFilter(Factory::getAccessGroupAgentFactory(), HealthCheckAgent::AGENT_ID, AccessGroupAgent::AGENT_ID),
      ],
      Factory::FILTER => [
        new ContainFilter(AccessGroupAgent::ACCESS_GROUP_ID, $accessGroups, Factory::getAccessGroupAgentFactory()),
      ]
    ];
  }
  
  public static function getToOneRelationships(): array {
    return [
      'agent' => [
        'key' => HealthCheckAgent::AGENT_ID,
        
        'relationType' => Agent::class,
        'relationKey' => Agent::AGENT_ID,
      ],
      'healthCheck' => [
        'key' => HealthCheckAgent::HEALTH_CHECK_ID,
        
        'relationType' => HealthCheck::class,
        'relationKey' => HealthCheck::HEALTH_CHECK_ID,
      ],
    ];
  }
  
  /**
   * @throws HttpError
   */
  protected function createObject(array $object): int {
    throw new HttpError("HealthCheckAgents cannot be created via API");
  }
  
  /**
   * @throws HttpError
   */
  public function updateObject(int $objectId, array $data): void {
    throw new HttpError("HealthCheckAgents cannot be updated via API");
  }
  
  /**
   * @throws HttpError
   */
  protected function deleteObject(object $object): void {
    /* Dummy code to implement abstract functions */
    throw new HttpError("HealthCheckAgents cannot be deleted via API");
  }
}
