<?php
use DBA\Factory;

use DBA\Agent;
use DBA\Speed;
use DBA\Task;

require_once(dirname(__FILE__) . "/../common/AbstractModelAPI.class.php");


class SpeedAPI extends AbstractModelAPI {
    public static function getBaseUri(): string {
      return "/api/v2/ui/speeds";
    }

    public static function getAvailableMethods(): array {
      return ['GET'];
    }

    public function getPermission(): string {
      // TODO: Find proper permission
      return DAccessControl::CREATE_HASHLIST_ACCESS;
    }

    public static function getDBAclass(): string {
      return Speed::class;
    }

    public function getExpandables(): array {
      return [ 'agent', 'task' ];
    }

    protected function fetchExpandObjects(array $objects, string $expand): mixed {     
      /* Ensure we receive the proper type */
      array_walk($objects, function($obj) { assert($obj instanceof Speed); });

      /* Expand requested section */
      switch($expand) {
        case 'agent':
          return $this->getForeignKeyRelation(
            $objects,
            Speed::AGENT_ID,
            Factory::getAgentFactory(),
            Agent::AGENT_ID
          );
        case 'task':
          return $this->getForeignKeyRelation(
            $objects,
            Speed::TASK_ID,
            Factory::getTaskFactory(),
            Task::TASK_ID
          );  
        default:
          throw new BadFunctionCallException("Internal error: Expansion '$expand' not implemented!");
      }
    }

    protected function createObject(array $data): int {
      assert(False, "Speeds cannot be created via API");
      return -1;
   }

   public function updateObject(object $object, array $data,  array $processed = []): void {
    assert(False, "Speeds cannot be updated via API");
   }

   protected function deleteObject(object $object): void {
     assert(False, "Speeds cannot be deleted via API");
   }
}

SpeedAPI::register($app);