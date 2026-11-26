<?php

namespace Hashtopolis\inc\utils;

use Exception;
use Hashtopolis\dba\models\NotificationSetting;
use Hashtopolis\dba\models\User;
use Hashtopolis\dba\Factory;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\inc\defines\DAccessControl;
use Hashtopolis\inc\defines\DNotificationObjectType;
use Hashtopolis\inc\defines\DNotificationType;
use Hashtopolis\inc\notifications\HashtopolisNotification;
use Hashtopolis\inc\HTException;
use Hashtopolis\inc\Login;

class NotificationUtils {
  /**
   * Creates a notification for the legacy UI, checking permissions through the AccessControl singleton.
   * APIv2 calls createNotificationSetting() instead, with a check against the request's token.
   *
   * @param string $actionType
   * @param string $notification
   * @param string $receiver
   * @param array $post
   * @param User|null $user the owner of the notification, the logged-in user when null
   * @return NotificationSetting
   * @throws HTException
   * @throws HttpError
   * @throws Exception
   */
  public static function createNotification(string $actionType, string $notification, string $receiver, array $post, ?User $user = null): NotificationSetting {
    if ($user == null) {
      $user = Login::getInstance()->getUser();
    }
    return self::createNotificationSetting($actionType, $notification, $receiver, $post, $user,
      fn(string|array $perm): bool => AccessControl::getInstance()->hasPermission($perm)
    );
  }
  
  /**
   * @param string $actionType one of DNotificationType
   * @param string $notification name of the HashtopolisNotification to send through
   * @param string $receiver
   * @param array $post the object the notification is about, under 'users', 'agents', 'hashlists' or 'tasks'
   *   depending on the action type; "ALL" or an id
   * @param User $user the owner of the notification
   * @param callable(string|array): bool $hasPermission tells whether the caller holds a DAccessControl
   *   permission (an array meaning any one of them)
   * @return NotificationSetting
   * @throws HTException
   * @throws HttpError
   * @throws Exception
   */
  public static function createNotificationSetting(string $actionType, string $notification, string $receiver, array $post, User $user, callable $hasPermission): NotificationSetting {
    $receiver = trim($receiver);
    if (!isset(HashtopolisNotification::getInstances()[$notification])) {
      throw new HttpError("This notification is not available!");
    }
    else if (!in_array($actionType, DNotificationType::getAll())) {
      throw new HttpError("This actionType is not available!");
    }
    else if (strlen($receiver) == 0) {
      throw new HttpError("You need to fill in a receiver!");
    }
    else if (!$hasPermission(DNotificationType::getRequiredPermission($actionType))) {
      throw new HttpError("You are not allowed to use this action type!");
    }
    $objectId = null;
    switch (DNotificationType::getObjectType($actionType)) {
      case DNotificationObjectType::USER:
        if (!$hasPermission(DAccessControl::USER_CONFIG_ACCESS)) {
          throw new HttpError("You are not allowed to use user action types!");
        }
        if ($post['users'] == "ALL") {
          break;
        }
        $objectId = UserUtils::getUser($post['users'])->getId();
        break;
      case DNotificationObjectType::AGENT:
        if ($post['agents'] == "ALL") {
          break;
        }
        $agent = AgentUtils::getAgent($post['agents']);
        $objectId = $agent->getId();
        break;
      case DNotificationObjectType::HASHLIST:
        if ($post['hashlists'] == "ALL") {
          break;
        }
        $hashlist = HashlistUtils::getHashlist($post['hashlists']);
        $objectId = $hashlist->getId();
        break;
      case DNotificationObjectType::TASK:
        if ($post['tasks'] == "ALL") {
          break;
        }
        $task = TaskUtils::getTask($post['tasks'], $user);
        $objectId = $task->getId();
        break;
    }
    
    $notificationSetting = new NotificationSetting(null, $actionType, $objectId, $notification, $user->getId(), $receiver, 1);
    return Factory::getNotificationSettingFactory()->save($notificationSetting);
  }
  
  /**
   * @param int $notification
   * @param boolean $isActive
   * @param boolean $doToggle
   * @param User $user
   * @throws HTException
   * @throws Exception
   */
  public static function setActive(int $notification, bool $isActive, bool $doToggle, User $user): void {
    $notification = NotificationUtils::getNotification($notification);
    if ($notification->getUserId() != $user->getId()) {
      throw new HTException("You have no access to this notification!");
    }
    if ($doToggle) {
      if ($notification->getIsActive() == 1) {
        $notification->setIsActive(0);
      }
      else {
        $notification->setIsActive(1);
      }
    }
    else {
      $notification->setIsActive(($isActive) ? 1 : 0);
    }
    Factory::getNotificationSettingFactory()->update($notification);
  }
  
  /**
   * @param int $notification
   * @param User $user
   * @throws HTException
   * @throws Exception
   */
  public static function delete(int $notification, User $user): void {
    $notification = NotificationUtils::getNotification($notification);
    if ($notification->getUserId() != $user->getId()) {
      throw new HTException("You are not allowed to delete this notification!");
    }
    Factory::getNotificationSettingFactory()->delete($notification);
  }
  
  /**
   * @param int $notification
   * @return NotificationSetting
   * @throws HTException
   * @throws Exception
   */
  public static function getNotification(int $notification): NotificationSetting {
    $notification = Factory::getNotificationSettingFactory()->get($notification);
    if ($notification == null) {
      throw new HTException("Notification not found!");
    }
    return $notification;
  }
}
