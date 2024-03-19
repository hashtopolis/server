<?php
use DBA\Factory;

use DBA\Chunk;
use DBA\Hash;
use DBA\Hashlist;

require_once(dirname(__FILE__) . "/../common/AbstractModelAPI.class.php");


class HashAPI extends AbstractModelAPI {
    public static function getBaseUri(): string {
      return "/api/v2/ui/hashes";
    }

    public static function getAvailableMethods(): array {
      return ['GET'];
    }

    public static function getDBAclass(): string {
      return Hash::class;
    }   

    public function getExpandables(): array {
      return ["hashlist", "chunk"];
    }

    protected function fetchExpandObjects(array $objects, string $expand): mixed {     
      /* Ensure we receive the proper type */
      array_walk($objects, function($obj) { assert($obj instanceof Hash); });

      /* Expand requested section */
      switch($expand) {
        case 'hashlist':
          return $this->getForeignKeyRelation(
            $objects,
            Hash::HASHLIST_ID,
            Factory::getHashListFactory(),
            HashList::HASHLIST_ID
          );
        case 'chunk':
          return $this->getForeignKeyRelation(
            $objects,
            Hash::CHUNK_ID,
            Factory::getChunkFactory(),
            Chunk::CHUNK_ID
          );
        default:
          throw new BadFunctionCallException("Internal error: Expansion '$expand' not implemented!");
      }
    }

    protected function createObject(array $data): int {
      /* Dummy code to implement abstract functions */
      assert(False, "Hashes cannot be created via API");
      return -1;
    }

    public function updateObject(object $object, array $data, array $processed = []): void {
       assert(False, "Hashes cannot be updated via API");
    }

    protected function deleteObject(object $object): void {
      /* Dummy code to implement abstract functions */
      assert(False, "Hashes cannot be deleted via API");
    }
}

HashAPI::register($app);