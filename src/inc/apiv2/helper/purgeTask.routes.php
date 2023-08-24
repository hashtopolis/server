<?php
use DBA\Chunk;
use DBA\Task;

require_once(dirname(__FILE__) . "/../common/AbstractHelperAPI.class.php");

class PurgeTaskHelperAPI extends AbstractHelperAPI {
  public static function getBaseUri(): string {
    return "/api/v2/helper/purgeTask";
  }

  public static function getAvailableMethods(): array {
    return ['POST'];
  }

  public function getRequiredPermissions(string $method): array
  {
    return [Chunk::PERM_DELETE, Task::PERM_UPDATE];
  }

  public function getFormFields(): array 
  {
    return  [
      Task::TASK_ID => ["type" => "int"],
    ];
  }

  public function actionPost($data): array|null {
    $pk = $data[Task::TASK_ID];
    $object = self::getOneTask($pk);

    TaskUtils::purgeTask($object->getId(), $this->getUser());   
    return null;
  }
}  

PurgeTaskHelperAPI::register($app);