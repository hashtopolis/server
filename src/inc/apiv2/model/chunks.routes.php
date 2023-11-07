<?php
use DBA\Factory;

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

    public static function getExpandables(): array {
      return ["task"];
    }

    protected static function fetchExpandObjects(array $objects, string $expand): mixed {     
      /* Ensure we receive the proper type */
      array_walk($objects, function($obj) { assert($obj instanceof Chunk); });

      /* Expand requested section */
      switch($expand) {
        case 'task':
          return self::getForeignKeyRelation(
            $objects,
            Chunk::TASK_ID,
            Factory::getTaskFactory(),
            Task::TASK_ID
          );
        default:
          throw new BadFunctionCallException("Internal error: Expansion '$expand' not implemented!");
      }
    }  

    protected function createObject(array $data): int {
      /* Dummy code to implement abstract functions */
      assert(False, "Chunks cannot be created via API");
      return -1;
    }

    public function updateObject(object $object, array $data, array $processed = []): void {
      assert(False, "Chunks cannot be updated via API");
    }

    protected function deleteObject(object $object): void {
      /* Dummy code to implement abstract functions */
      assert(False, "Chunks cannot be deleted via API");
    }
}

ChunkAPI::register($app);