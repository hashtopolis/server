<?php

namespace Hashtopolis\inc\apiv2\model;

use Exception;
use Hashtopolis\dba\AbstractModel;
use Hashtopolis\dba\ContainFilter;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\JoinFilter;
use Hashtopolis\dba\models\BrokenTask;
use Hashtopolis\dba\models\Hashlist;
use Hashtopolis\dba\models\Task;
use Hashtopolis\dba\models\TaskWrapper;
use Hashtopolis\dba\models\User;
use Hashtopolis\inc\apiv2\common\AbstractModelAPI;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\inc\utils\AccessUtils;
use Hashtopolis\inc\Util;


/**
 * @extends AbstractModelAPI<BrokenTask>
 */
class BrokenTaskAPI extends AbstractModelAPI {
  public static function getBaseUri(): string {
    return "/api/v2/ui/brokentasks";
  }

  /*
   * Include the task data for the broken task.
   */
  public static function getToOneRelationships(): array {
    return [
      'task' => [
        'key' => BrokenTask::TASK_ID,
        'relationType' => Task::class,
        'relationKey' => Task::TASK_ID,
      ],
    ];
  }

  public static function getAvailableMethods(): array {
    return ['GET', 'DELETE'];
  }

  public static function getDBAclass(): string {
    return BrokenTask::class;
  }

  /**
   * @param BrokenTask $object
   * @throws Exception
   */
  protected function getSingleACL(User $user, AbstractModel $object): bool {
    $accessGroupsUser = Util::arrayOfIds(AccessUtils::getAccessGroupsOfUser($user));
    $task = Factory::getTaskFactory()->get($object->getTaskId());
    if ($task === null) {
      return false;
    }
    $taskWrapper = Factory::getTaskWrapperFactory()->get($task->getTaskWrapperId());
    if ($taskWrapper === null) {
      return false;
    }
    $hashlist = Factory::getHashlistFactory()->get($taskWrapper->getHashlistId());
    if ($hashlist === null) {
      return false;
    }
    return in_array($hashlist->getAccessGroupId(), $accessGroupsUser);
  }

  /**
   * @throws Exception
   */
  protected function getFilterACL(): array {
    $accessGroups = Util::arrayOfIds(AccessUtils::getAccessGroupsOfUser($this->getCurrentUser()));

    return [
      Factory::JOIN => [
        new JoinFilter(Factory::getTaskFactory(), BrokenTask::TASK_ID, Task::TASK_ID),
        new JoinFilter(Factory::getTaskWrapperFactory(), Task::TASK_WRAPPER_ID, TaskWrapper::TASK_WRAPPER_ID, Factory::getTaskFactory()),
        new JoinFilter(Factory::getHashlistFactory(), TaskWrapper::HASHLIST_ID, Hashlist::HASHLIST_ID, Factory::getTaskWrapperFactory()),
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
    throw new HttpError("Broken tasks are created by the server and cannot be created via API");
  }

  /**
   * @throws HttpError
   */
  public function updateObject(int $objectId, array $data): void {
    throw new HttpError("Broken tasks cannot be updated via API");
  }

  /**
   * Deleting a broken-task entry clears the broken flag, so the task can be
   * assigned again (a manual retry after the operator has looked at it).
   *
   * @param BrokenTask $object
   * @throws Exception
   */
  protected function deleteObject(AbstractModel $object): void {
    Factory::getBrokenTaskFactory()->delete($object);
  }
}
