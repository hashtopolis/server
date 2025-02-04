<?php
use DBA\Factory;

use DBA\Agent;
use DBA\Chunk;
use DBA\Task;

require_once(dirname(__FILE__) . "/../common/AbstractModelAPI.class.php");


class ChunkAPI extends AbstractModelAPI {
    public static function getBaseUri(): string {
      return "/api/v2/ui/chunks";
    }

    public static function getAvailableMethods(): array {
      return ['GET'];
    }

    public static function getDBAclass(): string {
      return Chunk::class;
    }   

    public static function getToOneRelationships(): array {
      return [
        'agent' => [
          'key' => Chunk::AGENT_ID,
          
          'relationType' => Agent::class,
          'relationKey' => Agent::AGENT_ID,
        ],
        'task' => [
          'key' => Chunk::TASK_ID,
          
          'relationType' => Task::class,
          'relationKey' => Task::TASK_ID,
        ],
      ];
    }    

    protected function createObject(array $data): int {
      /* Dummy code to implement abstract functions */
      assert(False, "Chunks cannot be created via API");
      return -1;
    }

    public function updateObject(int $objectId, array $data): void {
      assert(False, "Chunks cannot be updated via API");
    }

    protected function deleteObject(object $object): void {
      /* Dummy code to implement abstract functions */
      assert(False, "Chunks cannot be deleted via API");
    }
}

ChunkAPI::register($app);