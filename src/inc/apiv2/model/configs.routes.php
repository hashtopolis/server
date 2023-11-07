<?php
use DBA\Factory;

use DBA\Config;
use DBA\ConfigSection;

require_once(dirname(__FILE__) . "/../common/AbstractModelAPI.class.php");


class ConfigAPI extends AbstractModelAPI {
    public static function getBaseUri(): string {
      return "/api/v2/ui/configs";
    }

    public static function getAvailableMethods(): array {
      return ['GET', 'PATCH'];
    }

    public static function getDBAclass(): string {
      return Config::class;
    }

    public static function getExpandables(): array {
      return ['configSection'];
    }

    public static function getToOneRelationships(): array {
      return [
        ['name' => 'configSection', 'key' => Config::CONFIG_SECTION_ID, 'relationType' => ConfigSection::class],
      ];
    }

    protected static function fetchExpandObjects(array $objects, string $expand): mixed {     
      /* Ensure we receive the proper type */
      array_walk($objects, function($obj) { assert($obj instanceof Config); });

      /* Expand requested section */
      switch($expand) {
        case 'configSection':
          return self::getForeignKeyRelation(
            $objects,
            Config::CONFIG_SECTION_ID,
            Factory::getConfigSectionFactory(),
            ConfigSection::CONFIG_SECTION_ID,
          );
        default:
          throw new BadFunctionCallException("Internal error: Expansion '$expand' not implemented!");
      }
    }
 
    protected function createObject(array $data): int {
       /* Dummy code to implement abstract functions */
       assert(False, "Configs cannot be created via API");
       return -1;
    }

    protected function deleteObject(object $object): void {
      /* Dummy code to implement abstract functions */
      assert(False, "Configs cannot be deleted via API");
    }
}

ConfigAPI::register($app);