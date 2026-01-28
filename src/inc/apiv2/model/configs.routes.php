<?php

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
    throw new HttpError("Configs cannot be created via API");
  }
  
  protected function deleteObject(object $object): void {
    throw new HttpError("Configs cannot be deleted via API");
  }
  
  /**
   * @throws HTException
   */
  protected function updateObjects(array $objects): void {
    ConfigUtils::updateConfigs($objects);
  }
}

use Slim\App;
/** @var App $app */
ConfigAPI::register($app);