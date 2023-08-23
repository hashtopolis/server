<?php
use DBA\Factory;
use DBA\Hash;
use DBA\QueryFilter;
use DBA\OrderFilter;

use DBA\HashType;

require_once(dirname(__FILE__) . "/../common/AbstractModelAPI.class.php");


class HashTypeAPI extends AbstractModelAPI {
    public static function getBaseUri(): string {
      return "/api/v2/ui/hashtypes";
    }

    public static function getDBAclass(): string {
      return HashType::class;
    }

    protected function getFactory(): object {
      return Factory::getHashTypeFactory();
    }

    protected function getFilterACL(): array {
      return [];
    }

    public function getFormFields(): array {
    // TODO Form declarations in more generic class to allow auto-generated OpenAPI specifications
    return  [];
    }

    protected function createObject(array $data): int {
      HashtypeUtils::addHashtype(
        $data[HashType::HASH_TYPE_ID],
        $data[HashType::DESCRIPTION],
        $data[HashType::IS_SALTED],
        $data[HashType::IS_SLOW_HASH],
        $this->getUser()
      );

      return $data[HashType::HASH_TYPE_ID];
    }

    protected function deleteObject(object $object): void {
      HashtypeUtils::deleteHashtype($object->getId());
    }
}

HashTypeAPI::register($app);