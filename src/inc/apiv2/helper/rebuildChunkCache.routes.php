<?php

use DBA\File;
use DBA\Config;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

require_once(dirname(__FILE__) . "/../common/AbstractHelperAPI.class.php");

class RebuildChunkCacheHelperAPI extends AbstractHelperAPI {
  public static function getBaseUri(): string {
    return "/api/v2/helper/rebuildChunkCache";
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
    return ["Rebuild" => "Success"];
  }
  
  /**
   * Endpoint to recount files for when there is size mismatch
   * @param $data
   * @return object|array|null
   * @throws ContainerExceptionInterface
   * @throws NotFoundExceptionInterface
   */
  public function actionPost($data): object|array|null {
    $result = ConfigUtils::rebuildCache();
    $response = $this->getResponse();
    $response["correctedChunks"] = $result[0];
    $response["correctedHashlists"] = $result[1];
    return $response;
  }
}

use Slim\App;
/** @var App $app */
RebuildChunkCacheHelperAPI::register($app);
