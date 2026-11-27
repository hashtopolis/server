<?php

namespace Hashtopolis\inc\apiv2\model;

use Hashtopolis\dba\AbstractModel;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\NotificationSetting;
use Hashtopolis\dba\models\User;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\inc\apiv2\common\AbstractModelAPI;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\inc\defines\DNotificationObjectType;
use Hashtopolis\inc\defines\DNotificationType;
use Hashtopolis\inc\HTException;
use Hashtopolis\inc\utils\NotificationUtils;

/**
 * @extends AbstractModelAPI<NotificationSetting>
 */
class NotificationSettingAPI extends AbstractModelAPI {
  public static function getBaseUri(): string {
    return "/api/v2/ui/notifications";
  }
  
  public static function getDBAclass(): string {
    return NotificationSetting::class;
  }
  
  public static function getToOneRelationships(): array {
    return [
      'user' => [
        'key' => NotificationSetting::USER_ID,
        // the owner of a notification setting cannot be changed,
        // delete the setting and create a new one instead
        'readonly' => true,
        
        'relationType' => User::class,
        'relationKey' => User::USER_ID,
      ],
    ];
  }
  
  /**
   * Notification settings are personal settings of the owning user, only the
   * owner (or an administrator, right group with permissions 'ALL') can see
   * and manage them.
   */
  protected function getFilterACL(): array {
    if ($this->getRightGroup($this->getCurrentUser()->getRightGroupId())->getPermissions() == 'ALL') {
      return [];
    }
    return [
      Factory::FILTER => [
        new QueryFilter(NotificationSetting::USER_ID, $this->getCurrentUser()->getId(), "=")
      ]
    ];
  }
  
  /**
   * @param NotificationSetting $object
   */
  protected function getSingleACL(User $user, AbstractModel $object): bool {
    return $object->getUserId() == $user->getId();
  }
  
  public function getFormFields(): array {
    return ['actionFilter' => ['type' => 'str(256)']];
  }
  
  /**
   * @throws HTException
   * @throws HttpError
   */
  protected function createObject(array $data): int {
    $dummyPost = [];
    switch (DNotificationType::getObjectType($data[NotificationSetting::ACTION])) {
      case DNotificationObjectType::USER:
        $dummyPost['users'] = $data['actionFilter'];
        break;
      case DNotificationObjectType::AGENT:
        $dummyPost['agents'] = $data['actionFilter'];
        break;
      case DNotificationObjectType::HASHLIST:
        $dummyPost['hashlists'] = $data['actionFilter'];
        break;
      case DNotificationObjectType::TASK:
        $dummyPost['tasks'] = $data['actionFilter'];
        break;
    }
    
    $notification = NotificationUtils::createNotification(
      $data[NotificationSetting::ACTION],
      $data[NotificationSetting::NOTIFICATION],
      $data[NotificationSetting::RECEIVER],
      $dummyPost,
      $this->getCurrentUser(),
      $data[NotificationSetting::IS_ACTIVE],
    );
    return $notification->getId();
  }
  
  /**
   * @param NotificationSetting $object
   * @throws HTException
   */
  protected function deleteObject(AbstractModel $object): void {
    NotificationUtils::delete($object->getId(), $this->getCurrentUser());
  }
  
  /**
   * The update handlers persist every attribute on its own, so all requested
   * changes are validated up front here: a rejected PATCH (or bulk PATCH)
   * leaves the setting completely unchanged instead of partially updated.
   *
   * @param int $objectId
   * @param array $data
   * @throws HTException
   * @throws HttpError
   */
  protected function updateObject(int $objectId, array $data): void {
    if (sizeof($data) > 0) {
      $notificationSetting = NotificationUtils::getNotification($objectId);
      NotificationUtils::assertOwner($notificationSetting, $this->getCurrentUser());
      NotificationUtils::validateUpdate($notificationSetting, $data);
    }
    parent::updateObject($objectId, $data);
  }

  /**
   * @param int $id
   * @param User $current_user
   */
  protected function getUpdateHandlers($id, $current_user): array {
    return [
      NotificationSetting::IS_ACTIVE => fn($value) => NotificationUtils::setActive($id, $value, false, $current_user),
      NotificationSetting::ACTION => fn($value) => NotificationUtils::updateAction($id, $value, $current_user),
      NotificationSetting::NOTIFICATION => fn($value) => NotificationUtils::updateNotification($id, $value, $current_user),
      NotificationSetting::RECEIVER => fn($value) => NotificationUtils::updateReceiver($id, $value, $current_user),
    ];
  }
}
