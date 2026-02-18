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
    throw new HttpError("ConfigSections cannot be created via API");
  }
  
  public function updateObject(int $objectId, array $data): void {
    throw new HttpError("ConfigSections cannot be updated via API");
  }
  
  protected function deleteObject(object $object): void {
    throw new HttpError("ConfigSections cannot be deleted via API");
  }
}

use Slim\App;
/** @var App $app */
ConfigSectionAPI::register($app);