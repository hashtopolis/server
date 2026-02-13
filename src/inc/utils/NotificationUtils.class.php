<?php

use DBA\NotificationSetting;
use DBA\User;
use DBA\Factory;

class NotificationUtils {
  /**
   * @param string $actionType
   * @param string $notification
   * @param string $receiver
   * @param array $post
   * @param User|null $user
   * @return NotificationSetting
   * @throws HTException
   * @throws HttpError
   */

  public static function createNotification(string $actionType, string $notification, string $receiver, array $post, ?User $user = null): NotificationSetting {
    if ($user == null) {
      $user = Login::getInstance()->getUser();
    };

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
    else if (!AccessControl::getInstance()->hasPermission(DNotificationType::getRequiredPermission($actionType))) {
            throw new HttpError("You are not allowed to use this action type!");
    }
    $objectId = null;
    switch (DNotificationType::getObjectType($actionType)) {
      case DNotificationObjectType::USER:
        if (!AccessControl::getInstance()->hasPermission(DAccessControl::USER_CONFIG_ACCESS)) {
          throw new HttpError("You are not allowed to use user action types!");
        }
        if ($post['users'] == "ALL") {
          break;
        }
        $user = UserUtils::getUser($post['users']);
        $objectId = $user->getId();
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
   */
  public static function setActive($notification, $isActive, $doToggle, $user) {
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
   */
  public static function delete($notification, $user) {
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
   */
  public static function getNotification($notification) {
    $notification = Factory::getNotificationSettingFactory()->get($notification);
    if ($notification == null) {
      throw new HTException("Notification not found!");
    }
    return $notification;
  }
}
