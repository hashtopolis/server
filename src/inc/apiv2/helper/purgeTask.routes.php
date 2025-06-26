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

  /**
   * taskId is the id of the task that should be purged.
   */
  public function getFormFields(): array 
  {
    return  [
      Task::TASK_ID => ["type" => "int"],
    ];
  }

  public static function getResponse(): array {
    return ["Purge" => "Success"];
  }
  
  /**
   * Endpoint to purge a task. Meaning all chunks of a task will be deleted and keyspace and progress will be set to 0.
   * @throws HTException
   */
  public function actionPost($data): object|array|null {
    $task = self::getTask($data[Task::TASK_ID]);

    TaskUtils::purgeTask($task->getId(), $this->getCurrentUser());   
    return $this->getResponse();
  }
}  

PurgeTaskHelperAPI::register($app);