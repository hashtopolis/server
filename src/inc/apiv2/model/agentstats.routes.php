<?php

use DBA\AccessGroupAgent;
use DBA\ContainFilter;
use DBA\Factory;

use DBA\AgentStat;
use DBA\JoinFilter;

require_once(dirname(__FILE__) . "/../common/AbstractModelAPI.class.php");


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
   
    protected function createObject(array $data): int {
      assert(False, "AgentStats cannot be created via API");
      return -1;
    }

    public function updateObject(int $objectId, array $data): void {
      assert(False, "AgentStats cannot be updated via API");
    }

    protected function deleteObject(object $object): void {
      Factory::getAgentStatFactory()->delete($object);
    }
}

AgentStatAPI::register($app);