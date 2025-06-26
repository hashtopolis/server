<?php

use DBA\AccessGroupAgent;
use DBA\ContainFilter;
use DBA\Factory;

use DBA\Agent;
use DBA\HealthCheck;
use DBA\HealthCheckAgent;
use DBA\JoinFilter;
use DBA\User;
use JetBrains\PhpStorm\NoReturn;

require_once(dirname(__FILE__) . "/../common/AbstractModelAPI.class.php");


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
    
    #[NoReturn] protected function createObject(array $object): int {
       assert(False, "HealthCheckAgents cannot be created via API");
    }

    #[NoReturn] public function updateObject(int $objectId, array $data): void {
      assert(False, "HealthCheckAgents cannot be updated via API");
   }

    #[NoReturn] protected function deleteObject(object $object): void {
      /* Dummy code to implement abstract functions */
      assert(False, "HealthCheckAgents cannot be deleted via API");
    }
}

HealthCheckAgentAPI::register($app);