<?php

use DBA\NotificationSetting;
use DBA\QueryFilter;

class NotificationHandler implements Handler {
  
  public function __construct($id = null) {
    // nothing required here
  }
  
  public function handle($action) {
    global $ACCESS_CONTROL;
    
    try {
      switch ($action) {
        case DNotificationAction::CREATE_NOTIFICATION:
          $ACCESS_CONTROL->checkPermission(DNotificationAction::CREATE_NOTIFICATION_PERM);
          NotificationUtils::createNotificaton($_POST['actionType'], $_POST['notification'], $_POST['receiver'], $_POST);
          break;
        case DNotificationAction::SET_ACTIVE:
          $ACCESS_CONTROL->checkPermission(DNotificationAction::SET_ACTIVE_PERM);
          NotificationUtils::setActive($_POST['notification'], false, true, $ACCESS_CONTROL->getUser());
          break;
        case DNotificationAction::DELETE_NOTIFICATION:
          $ACCESS_CONTROL->checkPermission(DNotificationAction::DELETE_NOTIFICATION_PERM);
          NotificationUtils::delete($_POST['notification'], $ACCESS_CONTROL->getUser());
          break;
        default:
          UI::addMessage(UI::ERROR, "Invalid action!");
          break;
      }
    }
    catch (HTException $e) {
      UI::addMessage(UI::ERROR, $e->getMessage());
    }
  }
  
  /**
   * @param $action
   * @param $payload DataSet
   */
  public static function checkNotifications($action, $payload) {
    /** @var $NOTIFICATIONS HashtopolisNotification[] */
    global $FACTORIES, $NOTIFICATIONS;
    
    $qF1 = new QueryFilter(NotificationSetting::ACTION, $action, "=");
    $qF2 = new QueryFilter(NotificationSetting::IS_ACTIVE, "1", "=");
    $notifications = $FACTORIES::getNotificationSettingFactory()->filter(array($FACTORIES::FILTER => array($qF1, $qF2)));
    foreach ($notifications as $notification) {
      if ($notification->getObjectId() != null) {
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
        if ($obj == 0 || $obj != $notification->getObjectId()) {
          continue;
        }
      }
      if ($action == DNotificationType::OWN_AGENT_ERROR) {
        if ($payload->getVal(DPayloadKeys::AGENT)->getUserId() != $notification->getUserId()) {
          continue;
        }
      }
      $NOTIFICATIONS[$notification->getNotification()]->execute($action, $payload, $notification);
    }
  }
}