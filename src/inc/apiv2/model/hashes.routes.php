<?php
use DBA\Factory;

use DBA\Hash;

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

    protected function getFactory(): object {
      return Factory::getHashFactory();
    }

    public function getExpandables(): array {
      return ["hashlist", "chunk"];
    }

    protected function doExpand(object $object, string $expand): mixed {
      assert($object instanceof Hash);
      switch($expand) {
        case 'hashlist':
          $obj = Factory::getHashListFactory()->get($object->getHashlistId());
          return $this->obj2Array($obj);
        case 'chunk':
          if (is_null($object->getChunkId())) {
            /* Chunk expansions are optional, hence the chunk object could be empty */
            return [];
          } else {
            $obj = Factory::getChunkFactory()->get($object->getChunkId());
            return $this->obj2Array($obj);
          }
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