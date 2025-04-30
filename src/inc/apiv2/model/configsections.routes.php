<?php
use DBA\ConfigSection;

require_once(dirname(__FILE__) . "/../common/AbstractModelAPI.class.php");


class ConfigSectionAPI extends AbstractModelAPI {
    public static function getBaseUri(): string {
      return "/api/v2/ui/configsections";
    }

    public static function getAvailableMethods(): array {
      return ['GET'];
    }

    public static function getDBAclass(): string {
      return ConfigSection::class;
    }   
 
    protected function createObject(array $data): int {
       /* Dummy code to implement abstract functions */
       assert(False, "ConfigSections cannot be created via API");
       return -1;
    }

    public function updateObject(int $objectId, array $data): void {
      assert(False, "ConfigSections cannot be updated via API");
    }

    protected function deleteObject(object $object): void {
      /* Dummy code to implement abstract functions */
      assert(False, "ConfigSections cannot be deleted via API");
    }
}

ConfigSectionAPI::register($app);