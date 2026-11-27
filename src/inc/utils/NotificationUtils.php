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
   * Notification settings are personal settings of the owning user, only the
   * owner (or an administrator, right group with permissions 'ALL') is allowed
   * to manage them.
   *
   * @param NotificationSetting $notificationSetting
   * @param User $user
   * @throws HTException
   */
  public static function assertOwner(NotificationSetting $notificationSetting, User $user): void {
    if ($notificationSetting->getUserId() == $user->getId()) {
      return;
    }
    $rightGroup = Factory::getRightGroupFactory()->get($user->getRightGroupId());
    if ($rightGroup->getPermissions() != 'ALL') {
      throw new HTException("You have no access to this notification!");
    }
  }

  /**
   * @param string $actionType
   * @param string $notification
   * @param string $receiver
   * @param array $post
   * @param User|null $user
   * @param bool $isActive
   * @return NotificationSetting
   * @throws HTException
   * @throws HttpError
   * @throws Exception
   */

  public static function createNotification(string $actionType, string $notification, string $receiver, array $post, ?User $user = null, bool $isActive = true): NotificationSetting {
    if ($user == null) {
      $user = Login::getInstance()->getUser();
    }
    
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
        $target = UserUtils::getUser($post['users']);
        $objectId = $target->getId();
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
    
    $notificationSetting = new NotificationSetting(null, $actionType, $objectId, $notification, $user->getId(), $receiver, $isActive ? 1 : 0);
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
    NotificationUtils::assertOwner($notification, $user);
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
    NotificationUtils::assertOwner($notification, $user);
    Factory::getNotificationSettingFactory()->delete($notification);
  }

  /**
   * Validates the requested attributes of a notification setting update
   * against the current state of the setting. NotificationSettingAPI calls
   * this before applying any of the changes, so a rejected update leaves the
   * setting completely unchanged.
   *
   * @param NotificationSetting $notificationSetting
   * @param array $data attributes to update as key-value pairs
   * @throws HttpError
   */
  public static function validateUpdate(NotificationSetting $notificationSetting, array $data): void {
    foreach ($data as $key => $value) {
      switch ($key) {
        case NotificationSetting::ACTION:
          NotificationUtils::validateAction($notificationSetting, $value);
          break;
        case NotificationSetting::NOTIFICATION:
          NotificationUtils::validateNotificationChannel($value);
          break;
        case NotificationSetting::RECEIVER:
          NotificationUtils::validateReceiver($value);
          break;
      }
    }
  }

  /**
   * An action can only be changed to another action of the same object type
   * while the setting is tied to an object, the permission required for the
   * new action is checked again.
   *
   * @param NotificationSetting $notificationSetting
   * @param string $action
   * @throws HttpError
   */
  public static function validateAction(NotificationSetting $notificationSetting, string $action): void {
    if (!in_array($action, DNotificationType::getAll())) {
      throw new HttpError("This actionType is not available!");
    }
    else if (!AccessControl::getInstance()->hasPermission(DNotificationType::getRequiredPermission($action))) {
      throw new HttpError("You are not allowed to use this action type!");
    }
    else if ($notificationSetting->getObjectId() != null
        && DNotificationType::getObjectType($action) != DNotificationType::getObjectType($notificationSetting->getAction())) {
      throw new HttpError("This notification is tied to an object, only actions of the same object type can be used!");
    }
  }

  /**
   * @param string $notification
   * @throws HttpError
   */
  public static function validateNotificationChannel(string $notification): void {
    if (!isset(HashtopolisNotification::getInstances()[$notification])) {
      throw new HttpError("This notification is not available!");
    }
  }

  /**
   * @param string $receiver
   * @throws HttpError
   */
  public static function validateReceiver(string $receiver): void {
    if (strlen(trim($receiver)) == 0) {
      throw new HttpError("You need to fill in a receiver!");
    }
  }

  /**
   * Change the action of a notification setting. Only the action column is
   * written, concurrent changes to the other columns are kept.
   *
   * @param int $notification
   * @param string $action
   * @param User $user
   * @throws HTException
   * @throws HttpError
   */
  public static function updateAction(int $notification, string $action, User $user): void {
    $notificationSetting = NotificationUtils::getNotification($notification);
    NotificationUtils::assertOwner($notificationSetting, $user);
    NotificationUtils::validateAction($notificationSetting, $action);
    Factory::getNotificationSettingFactory()->set($notificationSetting, NotificationSetting::ACTION, $action);
  }

  /**
   * @param int $notification
   * @param string $newNotification
   * @param User $user
   * @throws HTException
   * @throws HttpError
   */
  public static function updateNotification(int $notification, string $newNotification, User $user): void {
    $notificationSetting = NotificationUtils::getNotification($notification);
    NotificationUtils::assertOwner($notificationSetting, $user);
    NotificationUtils::validateNotificationChannel($newNotification);
    Factory::getNotificationSettingFactory()->set($notificationSetting, NotificationSetting::NOTIFICATION, $newNotification);
  }

  /**
   * @param int $notification
   * @param string $receiver
   * @param User $user
   * @throws HTException
   * @throws HttpError
   */
  public static function updateReceiver(int $notification, string $receiver, User $user): void {
    $notificationSetting = NotificationUtils::getNotification($notification);
    NotificationUtils::assertOwner($notificationSetting, $user);
    NotificationUtils::validateReceiver($receiver);
    Factory::getNotificationSettingFactory()->set($notificationSetting, NotificationSetting::RECEIVER, trim($receiver));
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
