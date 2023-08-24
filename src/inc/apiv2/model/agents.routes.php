<?php
use DBA\Factory;
use DBA\QueryFilter;
use DBA\JoinFilter;
use DBA\OrderFilter;

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

    protected function doExpand(object $object, string $expand): mixed {
      assert($object instanceof Agent);
      switch($expand) {
        case 'accessGroups':
          $qF = new QueryFilter(AccessGroupAgent::AGENT_ID, $object->getId(), "=", Factory::getAccessGroupAgentFactory());
          $jF = new JoinFilter(Factory::getAccessGroupAgentFactory(), AccessGroup::ACCESS_GROUP_ID, AccessGroupAgent::ACCESS_GROUP_ID);
          return $this->joinQuery(Factory::getAccessGroupFactory(), $qF, $jF);
        case 'agentstats':
          $qF = new QueryFilter(AgentStat::AGENT_ID, $object->getId(), "=");
          return $this->filterQuery(Factory::getAgentStatFactory(), $qF);
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