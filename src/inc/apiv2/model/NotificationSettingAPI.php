<?php

namespace Hashtopolis\inc\apiv2\model;

use Hashtopolis\dba\models\NotificationSetting;
use Hashtopolis\dba\models\User;
use Hashtopolis\inc\apiv2\common\AbstractModelAPI;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\inc\defines\DNotificationObjectType;
use Hashtopolis\inc\defines\DNotificationType;
use Hashtopolis\inc\HTException;
use Hashtopolis\inc\utils\NotificationUtils;

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
        
        'relationType' => User::class,
        'relationKey' => User::USER_ID,
      ],
    ];
  }
  
  function getAllPostParameters(array $features): array {
    $features = parent::getAllPostParameters($features);
    unset($features[NotificationSetting::IS_ACTIVE]);
    return $features;
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
    );
    return $notification->getId();
  }
  
  /**
   * @throws HTException
   */
  protected function deleteObject(object $object): void {
    NotificationUtils::delete($object->getId(), $this->getCurrentUser());
  }
  
  protected function getUpdateHandlers($id, $current_user): array {
    return [
      NotificationSetting::IS_ACTIVE => fn($value) => NotificationUtils::setActive($id, $value, false, $current_user),
    ];
  }
}
