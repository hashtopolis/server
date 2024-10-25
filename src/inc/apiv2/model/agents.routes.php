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

    public function getExpandables(): array {
      return ['accessGroups', 'agentstats'];
    }

    protected function fetchExpandObjects(array $objects, string $expand): mixed {     
      /* Ensure we receive the proper type */
      array_walk($objects, function($obj) { assert($obj instanceof Agent); });

      /* Expand requested section */
      switch($expand) {
        case 'accessGroups':
          return $this->getManyToManyRelation(
            $objects,
            Agent::AGENT_ID,
            Factory::getAccessGroupAgentFactory(),
            AccessGroupAgent::AGENT_ID,
            Factory::getAccessGroupFactory(),
            AccessGroup::ACCESS_GROUP_ID
          );
        case 'agentstats':
          return $this->getManyToOneRelation(
            $objects,
            Agent::AGENT_ID,
            Factory::getAgentStatFactory(),
            AgentStat::AGENT_ID
          );
        default:
          throw new BadFunctionCallException("Internal error: Expansion '$expand' not implemented!");
      }
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