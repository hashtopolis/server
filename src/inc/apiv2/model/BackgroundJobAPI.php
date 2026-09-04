<?php

namespace Hashtopolis\inc\apiv2\model;

use Hashtopolis\dba\AbstractModel;
use Hashtopolis\dba\models\BackgroundJob;
use Hashtopolis\dba\models\User;
use Hashtopolis\inc\apiv2\common\AbstractModelAPI;
use Hashtopolis\inc\apiv2\error\HttpConflict;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\inc\defines\DBackgroundJobStatus;
use Hashtopolis\inc\utils\BackgroundJobUtils;

/**
 * @extends AbstractModelAPI<BackgroundJob>
 */
class BackgroundJobAPI extends AbstractModelAPI {
  public static function getBaseUri(): string {
    return "/api/v2/ui/backgroundJobs";
  }

  public static function getAvailableMethods(): array {
    return ['GET', 'DELETE'];
  }

  public static function getDBAclass(): string {
    return BackgroundJob::class;
  }

  public static function getToOneRelationships(): array {
    return [
      'user' => [
        'key' => BackgroundJob::USER_ID,

        'relationType' => User::class,
        'relationKey' => User::USER_ID,
      ],
    ];
  }

  /**
   * @throws HttpError
   */
  protected function createObject(array $data): int {
    throw new HttpError("Background jobs cannot be created via API");
  }

  /**
   * @throws HttpError
   */
  public function updateObject(int $objectId, array $data): void {
    throw new HttpError("Background jobs cannot be updated via API");
  }

  /**
   * @param BackgroundJob $object
   * @throws HttpConflict
   */
  protected function deleteObject(AbstractModel $object): void {
    if ($object->getStatus() === DBackgroundJobStatus::RUNNING) {
      throw new HttpConflict("Background job is currently running and cannot be deleted!");
    }
    BackgroundJobUtils::deleteJob($object);
  }
}
