<?php

namespace Hashtopolis\inc\apiv2\model;

use Hashtopolis\inc\utils\ConfigUtils;
use Hashtopolis\dba\models\Config;
use Hashtopolis\dba\models\ConfigSection;
use Hashtopolis\inc\apiv2\common\AbstractModelAPI;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\inc\HTException;


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
  
  /**
   * @throws HttpError
   */
  protected function createObject(array $data): int {
    throw new HttpError("Configs cannot be created via API");
  }
  
  /**
   * @throws HttpError
   */
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
