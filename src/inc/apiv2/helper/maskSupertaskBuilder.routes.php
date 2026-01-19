<?php

use DBA\Pretask;
use DBA\Supertask;

require_once(dirname(__FILE__) . "/../common/AbstractHelperAPI.class.php");

class MaskSupertaskBuilderHelperAPI extends AbstractHelperAPI {
  public static function getBaseUri(): string {
    return "/api/v2/helper/maskSupertaskBuilder";
  }
  
  public static function getAvailableMethods(): array {
    return ['POST'];
  }
  
  public function getRequiredPermissions(string $method): array {
    return [Pretask::PERM_CREATE, Supertask::PERM_CREATE];
  }
  
  public function getFormFields(): array {
    return [
      "name" => ['type' => 'str'],
      "isCpu" => ['type' => 'bool'],
      "isSmall" => ['type' => 'bool'],
      "optimized" => ['type' => 'bool'],
      "crackerBinaryTypeId" => ['type' => 'int'],
      "benchtype" => ['type' => 'str'],
      "masks" => ['type' => 'str'],
      "maxAgents" => ['type' => 'int'],
    ];
  }
  
  public static function getResponse(): string {
    return "Supertask";
  }
  
  /**
   * Endpoint to import cracked hashes into a hashlist.
   * @throws HTException
   */
  public function actionPost($data): object|array|null {
    return SupertaskUtils::importSupertask($data['name'], $data['isCpu'], $data['maxAgents'], $data['isSmall'], $data['optimized'], $data['crackerBinaryTypeId'], explode("\n", str_replace("\r\n", "\n", $data['masks'])), $data['benchtype']);
  }
}

MaskSupertaskBuilderHelperAPI::register($app);