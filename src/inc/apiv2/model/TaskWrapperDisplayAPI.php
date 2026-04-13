<?php

namespace Hashtopolis\inc\apiv2\model;

use Hashtopolis\inc\utils\AccessUtils;
use Hashtopolis\dba\ContainFilter;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\JoinFilter;
use Hashtopolis\dba\models\Hashlist;
use Hashtopolis\dba\models\Task;
use Hashtopolis\dba\models\TaskWrapper;
use Hashtopolis\dba\models\TaskWrapperDisplay;
use Hashtopolis\dba\models\User;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\inc\apiv2\common\AbstractModelAPI;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\inc\Util;


class TaskWrapperDisplayAPI extends AbstractModelAPI {
  public static function getBaseUri(): string {
    return "/api/v2/ui/taskwrapperdisplays";
  }

  public static function getAvailableMethods(): array {
    return ['GET'];
  }
  public function getRequiredPermissions(string $method): array {
    return [Task::PERM_READ, TaskWrapper::PERM_READ];
  }

  public static function getDBAclass(): string {
    return TaskWrapperDisplay::class;
  }

  protected function getSingleACL(User $user, object $object): bool {
    $accessGroupsUser = Util::arrayOfIds(AccessUtils::getAccessGroupsOfUser($user));
    
    $qF1 = new ContainFilter(Hashlist::ACCESS_GROUP_ID, $accessGroupsUser, Factory::getHashlistFactory());
    $qF2 = new QueryFilter(TaskWrapper::TASK_WRAPPER_ID, $object->getId(), "=");
    $jF = new JoinFilter(Factory::getHashlistFactory(), TaskWrapper::HASHLIST_ID, Hashlist::HASHLIST_ID);
    $wrappers = Factory::getTaskWrapperFactory()->filter([Factory::FILTER => [$qF1, $qF2], Factory::JOIN => $jF])[Factory::getTaskWrapperFactory()->getModelName()];
    return count($wrappers) > 0;
  }

  protected function getFilterACL(): array {

    $accessGroups = Util::arrayOfIds(AccessUtils::getAccessGroupsOfUser($this->getCurrentUser()));
    
    return [
      Factory::JOIN => [
        new JoinFilter(Factory::getHashlistFactory(), TaskWrapperDisplay::HASHLIST_ID, Hashlist::HASHLIST_ID),
      ],
      Factory::FILTER => [
        new ContainFilter(Hashlist::ACCESS_GROUP_ID, $accessGroups, Factory::getHashlistFactory()),
      ]
    ];
  }

  /**
   * @throws HttpError
   */
  protected function createObject(array $data): int {
    throw new HttpError("TaskWrapperDisplays cannot be created via API");
  }

  /**
   * @throws HttpError
   */
  public function updateObject(int $objectId, array $data): void {
    throw new HttpError("TaskWrapperDisplays cannot be updated via API");
  }

  /**
   * @throws HttpError
   */
  protected function deleteObject(object $object): void {
    throw new HttpError("TaskWrapperDisplays cannot be deleted via API");
  }

  public static function getToManyRelationships(): array {
    return [
      'tasks' => [
        'key' => TaskWrapperDisplay::TASK_WRAPPER_ID,
        
        'relationType' => Task::class,
        'relationKey' => Task::TASK_WRAPPER_ID,
        'readonly' => true // Not allowed to change tasks of a taskwrapper 
      ],
    ];
  }
}
