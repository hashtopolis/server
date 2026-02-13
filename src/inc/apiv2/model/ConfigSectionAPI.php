<?php

namespace Hashtopolis\inc\apiv2\model;

use Hashtopolis\dba\models\ConfigSection;
use Hashtopolis\inc\apiv2\common\AbstractModelAPI;
use Hashtopolis\inc\apiv2\common\error\HttpError;


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
  
  /**
   * @throws HttpError
   */
  protected function createObject(array $data): int {
    throw new HttpError("ConfigSections cannot be created via API");
  }
  
  /**
   * @throws HttpError
   */
  public function updateObject(int $objectId, array $data): void {
    throw new HttpError("ConfigSections cannot be updated via API");
  }
  
  /**
   * @throws HttpError
   */
  protected function deleteObject(object $object): void {
    throw new HttpError("ConfigSections cannot be deleted via API");
  }
}
