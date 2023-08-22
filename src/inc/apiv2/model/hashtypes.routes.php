<?php
use DBA\Factory;
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

    protected function createObject($mappedQuery, $QUERY): int {
      /* Parameter is used as primary key in database */
      $hashtypeId = $mappedQuery[HashType::HASH_TYPE_ID];

      HashtypeUtils::addHashtype(
        $hashtypeId,
        $mappedQuery[HashType::DESCRIPTION],
        $mappedQuery[HashType::IS_SALTED],
        $mappedQuery[HashType::IS_SLOW_HASH],
        $this->getUser()
      );

      /* On succesfully insert, return ID */
      return $hashtypeId;
    }

    protected function deleteObject(object $object): void {
      HashtypeUtils::deleteHashtype($object->getId());
    }
}

HashTypeAPI::register($app);