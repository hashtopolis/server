<?php

use DBA\Pretask;
use DBA\Supertask;

require_once(dirname(__FILE__) . "/../common/AbstractHelperAPI.class.php");

class BulkSupertaskBuilderHelperAPI extends AbstractHelperAPI {
  public static function getBaseUri(): string {
    return "/api/v2/helper/bulkSupertaskBuilder";
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
      "crackerBinaryTypeId" => ['type' => 'int'],
      "benchtype" => ['type' => 'str'],
      "command" => ['type' => 'str'],
      "maxAgents" => ['type' => 'int'],
      "basefiles" => ["type" => "array", "subtype" => "int"],
      "iterfiles" => ["type" => "array", "subtype" => "int"],
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
    return SupertaskUtils::bulkSupertask($data['name'], $data['command'], $data['isCpu'], $data['maxAgents'], $data['isSmall'], $data['crackerBinaryTypeId'], $data['benchtype'], $data['basefiles'], $data['iterfiles'], Login::getInstance()->getUser());
  }
}

BulkSupertaskBuilderHelperAPI::register($app);