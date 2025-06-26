<?php
use DBA\ConfigSection;
use JetBrains\PhpStorm\NoReturn;

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
 
    #[NoReturn] protected function createObject(array $data): int {
       assert(False, "ConfigSections cannot be created via API");
    }

    #[NoReturn] public function updateObject(int $objectId, array $data): void {
      assert(False, "ConfigSections cannot be updated via API");
    }

    #[NoReturn] protected function deleteObject(object $object): void {
      assert(False, "ConfigSections cannot be deleted via API");
    }
}

ConfigSectionAPI::register($app);