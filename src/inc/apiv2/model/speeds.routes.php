<?php
use DBA\Factory;

use DBA\Speed;

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

    protected function doExpand(object $object, string $expand): mixed {
      assert($object instanceof Speed);
      switch($expand) {
        case 'agent':
          $obj = Factory::getAgentFactory()->get($object->getAgentId());
          return $this->obj2Array($obj);
        case 'task':
          $obj = Factory::getTaskFactory()->get($object->getTaskId());
          return $this->obj2Array($obj);
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