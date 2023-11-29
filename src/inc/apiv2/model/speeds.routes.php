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


    public static function getToOneRelationships(): array {
      return [
        'agent' => [
          'key' => Speed::AGENT_ID, 

          'relationType' => Agent::class,
          'relationKey' => Agent::AGENT_ID,
        ],
        'task' => [
          'key' => Speed::TASK_ID, 

          'relationType' => Task::class,
          'relationKey' => Task::TASK_ID,
        ],
      ];
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