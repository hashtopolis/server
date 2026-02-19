<?php

use DBA\Factory;
use DBA\OrderFilter;
use DBA\QueryFilter;

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
  
  public function getRequiredPermissions(string $method): array {
    return [TaskWrapper::PERM_CREATE, Task::PERM_CREATE, Supertask::PERM_READ, Hashlist::PERM_READ, CrackerBinary::PERM_READ];
  }
  
  /**
   * supertaskTemplateId is the the Id of the supertasktemplate of which you want to create a supertask of.
   * hashlistId is the Id of the hashlist that has to be used for the supertask.
   * crackerVersionId is the Id of the crackerversion that is used for the created supertask.
   */
  public function getFormFields(): array {
    return [
      "supertaskTemplateId" => ["type" => "int"],
      Hashlist::HASHLIST_ID => ["type" => "int"],
      "crackerVersionId" => ["type" => "int"],
    ];
  }
  
  public static function getResponse(): string {
    return "TaskWrapper";
  }
  
  /**
   * Endpoint to create a supertask from a supertask template
   * @throws HTException
   */
  public function actionPost($data): object|array|null {
    $supertaskTemplate = self::getSupertask($data["supertaskTemplateId"]);
    $hashlist = self::getHashlist($data[Hashlist::HASHLIST_ID]);
    $crackerBinary = self::getCrackerBinary($data["crackerVersionId"]);
    
    SupertaskUtils::runSupertask(
      $supertaskTemplate->getId(),
      $hashlist->getId(),
      $crackerBinary->getId()
    );
    
    /* Quick to retrieve newly created TaskWrapper */
    $qFs = [
      new QueryFilter(TaskWrapper::HASHLIST_ID, $hashlist->getId(), "="),
      new QueryFilter(TaskWrapper::TASK_TYPE, DTaskTypes::SUPERTASK, "=")
    ];
    $oF = new OrderFilter(TaskWrapper::TASK_WRAPPER_ID, "DESC");
    
    $objects = self::getModelFactory(TaskWrapper::class)->filter([Factory::FILTER => $qFs, Factory::ORDER => $oF]);
    assert(count($objects) > 0);
    
    return $objects[0];
  }
}

use Slim\App;
/** @var App $app */
CreateSupertaskHelperAPI::register($app);