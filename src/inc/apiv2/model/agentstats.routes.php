<?php
use DBA\Factory;

use DBA\AgentStat;

require_once(dirname(__FILE__) . "/../common/AbstractModelAPI.class.php");


class AgentStatsAPI extends AbstractModelAPI {
    public static function getBaseUri(): string {
      return "/api/v2/ui/agentstats";
    }

    public static function getAvailableMethods(): array {
      return ['GET', 'DELETE'];
    }

    public static function getDBAclass(): string {
      return AgentStat::class;
    }
   
    protected function createObject(array $data): int {
      assert(False, "AgentStats cannot be created via API");
      return -1;
    }

    public function updateObject(object $object, array $data, array $processed = []): void {
      assert(False, "AgentStats cannot be updated via API");
    }

    protected function deleteObject(object $object): void {
      Factory::getAgentStatFactory()->delete($object);
    }
}

AgentStatsAPI::register($app);