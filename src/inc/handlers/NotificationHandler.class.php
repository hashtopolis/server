<?php

use DBA\NotificationSetting;
use DBA\QueryFilter;
use DBA\Factory;
use DBA\User;

class NotificationHandler implements Handler {
  
  public function __construct($id = null) {
    // nothing required here
  }
  
  public function handle($action) {
    try {
      switch ($action) {
        case DNotificationAction::CREATE_NOTIFICATION:
          AccessControl::getInstance()->checkPermission(DNotificationAction::CREATE_NOTIFICATION_PERM);
          NotificationUtils::createNotificaton($_POST['actionType'], $_POST['notification'], $_POST['receiver'], $_POST);
          break;
        case DNotificationAction::SET_ACTIVE:
          AccessControl::getInstance()->checkPermission(DNotificationAction::SET_ACTIVE_PERM);
          NotificationUtils::setActive($_POST['notification'], false, true, Login::getInstance()->getUser());
          break;
        case DNotificationAction::DELETE_NOTIFICATION:
          AccessControl::getInstance()->checkPermission(DNotificationAction::DELETE_NOTIFICATION_PERM);
          NotificationUtils::delete($_POST['notification'], Login::getInstance()->getUser());
          break;
        default:
          UI::addMessage(UI::ERROR, "Invalid action!");
          break;
      }
    }
    catch (Exception $e) {
      UI::addMessage(UI::ERROR, $e->getMessage());
    }
  }
  
  /**
   * @param $action
   * @param $payload DataSet
   */
  public static function checkNotifications($action, $payload) {
    $qF1 = new QueryFilter(NotificationSetting::ACTION, $action, "=");
    $qF2 = new QueryFilter(NotificationSetting::IS_ACTIVE, "1", "=");
    $notifications = Factory::getNotificationSettingFactory()->filter([Factory::FILTER => [$qF1, $qF2]]);
    foreach ($notifications as $notification) {
      DServerLog::log(DServerLog::TRACE, "Checking if we should send notification: "
        . $action . ":" . $notification->getUserId());
      try {
        if ($notification->getObjectId() != null) {
          if (!self::matchesObjectInNotification($notification, $payload)) {
            DServerLog::log(DServerLog::TRACE, "Discarding notification. Object does not match");
            continue;
          }
        }
        if ($action == DNotificationType::OWN_AGENT_ERROR) {
          if ($payload->getVal(DPayloadKeys::AGENT)->getUserId() != $notification->getUserId()) {
            DServerLog::log(DServerLog::TRACE, "Discarding own agent notification. Agent not belonging to user.");
            continue;
          }
        }
        if (!self::isAuthorizedToReceiveNotification($action, $notification, $payload)) {
          DServerLog::log(DServerLog::TRACE, "Discarding notification. User not authorized.");
          continue;
        }

        DServerLog::log(DServerLog::TRACE, "Sending notification", [$notification, $payload]);
        HashtopolisNotification::getInstances()[$notification->getNotification()]->execute($action, $payload, $notification);
      } catch (Throwable $e) {
        DServerLog::log(DServerLog::ERROR, "Failed to send notification", [$e->getMessage(), $e->getTraceAsString()]);
      }
    }
  }

  private static function isAuthorizedToReceiveNotification($action, $notification, $payload): bool {
    switch ($action) {
      // Hashlists
      case DNotificationType::HASHLIST_ALL_CRACKED:
      case DNotificationType::HASHLIST_CRACKED_HASH:
      case DNotificationType::DELETE_HASHLIST:
      case DNotificationType::NEW_HASHLIST:
        $hashlist = $payload->getVal(DPayloadKeys::HASHLIST);
        return AccessUtils::userCanAccessHashlists($hashlist, self::getUserFromNotification($notification));

      // Tasks
      case DNotificationType::TASK_COMPLETE:
      case DNotificationType::DELETE_TASK:
      case DNotificationType::NEW_TASK:
        $task = $payload->getVal(DPayloadKeys::TASK);
        $taskWrapper = Factory::getTaskWrapperFactory()->get($task->getTaskWrapperId());
        return AccessUtils::userCanAccessTask($taskWrapper, self::getUserFromNotification($notification));

      // Agents
      case DNotificationType::AGENT_ERROR:
      case DNotificationType::OWN_AGENT_ERROR:
      case DNotificationType::DELETE_AGENT:
        $agent = $payload->getVal(DPayloadKeys::AGENT);
        return AccessUtils::userCanAccessAgent($agent, self::getUserFromNotification($notification));

      case DNotificationType::NEW_AGENT:
        $accessControl = AccessControl::getInstance(self::getUserFromNotification($notification));
        return $accessControl->hasPermission(DAccessControl::MANAGE_AGENT_ACCESS);

      // Users
      case DNotificationType::USER_DELETED:
      case DNotificationType::USER_LOGIN_FAILED:
      case DNotificationType::USER_CREATED:
        $accessControl = AccessControl::getInstance(self::getUserFromNotification($notification));
        return $accessControl->hasPermission(DAccessControl::USER_CONFIG_ACCESS);

      case DNotificationType::LOG_ERROR:
      case DNotificationType::LOG_WARN:
      case DNotificationType::LOG_FATAL:
        $accessControl = AccessControl::getInstance(self::getUserFromNotification($notification));
        return $accessControl->hasPermission(DAccessControl::SERVER_CONFIG_ACCESS);
    }

    return false;
  }

  private static function matchesObjectInNotification($notification, $payload): bool {
    $obj = 0;
    switch (DNotificationType::getObjectType($notification->getAction())) {
      case DNotificationObjectType::USER:
        $obj = $payload->getVal(DPayloadKeys::USER)->getId();
        break;
      case DNotificationObjectType::AGENT:
        $obj = $payload->getVal(DPayloadKeys::AGENT)->getId();
        break;
      case DNotificationObjectType::HASHLIST:
        $obj = $payload->getVal(DPayloadKeys::HASHLIST)->getId();
        break;
      case DNotificationObjectType::TASK:
        $obj = $payload->getVal(DPayloadKeys::TASK)->getId();
        break;
    }
    return $obj != 0 && $obj == $notification->getObjectId();
  }

  private static function getUserFromNotification($notification): User {
    return Factory::getUserFactory()->get($notification->getUserId());
  }
}