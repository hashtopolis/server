<?php

use DBA\AgentError;
use DBA\Factory;

require_once(dirname(__FILE__) . "/../common/AbstractModelAPI.class.php");


class AgentErrorAPI extends AbstractModelAPI {
    public static function getBaseUri(): string {
      return "/api/v2/ui/agenterrors";
    }

    public static function getAvailableMethods(): array {
      return ['GET', 'DELETE'];
    }

    public static function getDBAclass(): string {
      return AgentError::class;
    }
   
    protected function createObject(array $data): int {
      assert(False, "AgentErrors cannot be created via API");
      return -1;
    }

    public function updateObject(int $objectId, array $data): void {
      assert(False, "AgentErrors cannot be updated via API");
    }

    protected function deleteObject(object $object): void {
      Factory::getAgentErrorFactory()->delete($object);
    }
}

AgentErrorAPI::register($app);