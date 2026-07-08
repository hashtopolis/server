<?php

namespace Hashtopolis\inc\apiv2\helper;

use Hashtopolis\dba\AbstractModel;
use Hashtopolis\dba\models\Chunk;
use Hashtopolis\dba\models\Task;
use Hashtopolis\inc\apiv2\common\AbstractHelperAPI;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\inc\apiv2\error\ResourceNotFoundError;
use Hashtopolis\inc\HTException;
use Hashtopolis\inc\utils\TaskUtils;

class PurgeTaskHelperAPI extends AbstractHelperAPI {
  public static function getBaseUri(): string {
    return "/api/v2/helper/purgeTask";
  }
  
  public static function getAvailableMethods(): array {
    return ['POST'];
  }
  
  public function getRequiredPermissions(string $method): array {
    return [Chunk::PERM_DELETE, Task::PERM_UPDATE];
  }
  
  /**
   * taskId is the id of the task that should be purged.
   */
  public function getFormFields(): array {
    return [
      Task::TASK_ID => ["type" => "int"],
    ];
  }
  
  public static function getResponse(): array {
    return ["Purge" => "Success"];
  }
  
  /**
   * Endpoint to purge a task. Meaning all chunks of a task will be deleted and keyspace and progress will be set to 0.
   * @param $data
   * @return AbstractModel|array|null
   * @throws HTException
   * @throws HttpError
   * @throws ResourceNotFoundError
   */
  public function actionPost($data): AbstractModel|array|null {
    $task = self::getTask($data[Task::TASK_ID]);
    
    TaskUtils::purgeTask($task->getId(), $this->getCurrentUser());
    return $this->getResponse();
  }
}