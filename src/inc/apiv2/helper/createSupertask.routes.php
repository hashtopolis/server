<?php

use DBA\CrackerBinary;
use DBA\Hashlist;
use DBA\Supertask;
use DBA\Task;
use DBA\TaskWrapper;

require_once(dirname(__FILE__) . "/../common/AbstractHelperAPI.class.php");

class CreateSupertaskHelperAPI extends AbstractHelperAPI {
  public static function getBaseUri(): string {
    return "/api/v2/helper/createSupertask";
  }

  public static function getAvailableMethods(): array {
    return ['POST'];
  }

  public function getRequiredPermissions(string $method): array
  {
    return [TaskWrapper::PERM_CREATE, Task::PERM_CREATE, Supertask::PERM_READ, Hashlist::PERM_READ, CrackerBinary::PERM_READ];
  }

  public function getFormFields(): array 
  {
    return  [
      "supertaskTemplateId" => ["type" => "int"],
      Hashlist::HASHLIST_ID => ["type" => "int"],
      "crackerVersionId" => ["type" => "int"],
    ];
  }

  public function actionPost($data): array|null {
    $supertaskTemplate = self::getSupertask($data["supertaskTemplateId"]);
    $hashlist = self::getHashlist($data[Hashlist::HASHLIST_ID]);
    $crackerBinary = self::getCrackerBinary($data["crackerVersionId"]);

    SupertaskUtils::runSupertask(
        $supertaskTemplate->getId(),
        $hashlist->getId(),
        $crackerBinary->getId()
    );

    return ['rick' => 'foo'];
  }
}  

CreateSupertaskHelperAPI::register($app);