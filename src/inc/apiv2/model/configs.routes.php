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

    public static function getToOneRelationships(): array {
      return [
        'configSection' => [
          'key' => Config::CONFIG_SECTION_ID, 

          'relationType' => ConfigSection::class,
          'relationKey' => ConfigSection::CONFIG_SECTION_ID,
        ],
      ];
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

    protected function updateObjects(array $objects) {
      ConfigUtils::updateConfigs($objects);
    }
}

ConfigAPI::register($app);