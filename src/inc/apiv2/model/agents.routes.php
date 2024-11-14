<?php
use DBA\Factory;

use DBA\AccessGroup;
use DBA\AccessGroupAgent;
use DBA\Agent;
use DBA\AgentStat;

require_once(dirname(__FILE__) . "/../common/AbstractModelAPI.class.php");


class AgentAPI extends AbstractModelAPI {
    public static function getBaseUri(): string {
      return "/api/v2/ui/agents";
    }

    public static function getAvailableMethods(): array {
      return ['GET', 'PATCH', 'DELETE'];
    }

    public static function getDBAclass(): string {
      return Agent::class;
    }

    public static function getToManyRelationships(): array {
      return [
        'accessGroups' => [
          'key' => Agent::AGENT_ID,
          
          'junctionTableType' => AccessGroupAgent::class,
          'junctionTableFilterField' => AccessGroupAgent::AGENT_ID,
          'junctionTableJoinField' => AccessGroupAgent::ACCESS_GROUP_ID,

          'relationType' => AccessGroup::class,
          'relationKey' => AccessGroup::ACCESS_GROUP_ID,        
        ],
        'agentStats' => [
          'key' => Agent::AGENT_ID,
          
          'relationType' => AgentStat::class,
          'relationKey' => AgentStat::AGENT_ID,        
        ],

      ];
    }
   
    protected function createObject(array $data): int {
      assert(False, "Chunks cannot be created via API");
      return -1;
    }

    protected function deleteObject(object $object): void {
      AgentUtils::delete($object->getId(), $this->getCurrentUser());
    }
}

AgentAPI::register($app);