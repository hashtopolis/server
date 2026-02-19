<?php

use DBA\File;
use DBA\Config;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

require_once(dirname(__FILE__) . "/../common/AbstractHelperAPI.class.php");

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
   * @throws ContainerExceptionInterface
   * @throws NotFoundExceptionInterface
   * @throws HTMessages
   */
  public function actionPost($data): object|array|null {
    ConfigUtils::scanFiles();
    return $this->getResponse();
  }
}

use Slim\App;
/** @var App $app */
RescanGlobalFilesHelperAPI::register($app);
