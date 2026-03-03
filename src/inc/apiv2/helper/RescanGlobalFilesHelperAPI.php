<?php

namespace Hashtopolis\inc\apiv2\helper;

use Hashtopolis\inc\utils\ConfigUtils;
use Hashtopolis\dba\models\Config;
use Hashtopolis\inc\apiv2\common\AbstractHelperAPI;
use Hashtopolis\inc\HTMessages;

class RescanGlobalFilesHelperAPI extends AbstractHelperAPI {
  public static function getBaseUri(): string {
    return "/api/v2/helper/rescanGlobalFiles";
  }
  
  public static function getAvailableMethods(): array {
    return ['POST'];
  }
  
  public function getRequiredPermissions(string $method): array {
    return [Config::PERM_UPDATE];
  }
  
  public function getFormFields(): array {
    return [];
  }
  
  public static function getResponse(): array {
    return ["Rescan" => "Success"];
  }
  
  /**
   * Endpoint to recount files for when there is size mismatch
   * @param $data
   * @return object|array|null
   * @throws HTMessages
   */
  public function actionPost($data): object|array|null {
    ConfigUtils::scanFiles();
    return $this->getResponse();
  }
}

