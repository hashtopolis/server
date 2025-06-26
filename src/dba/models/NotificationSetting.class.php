<?php

namespace DBA;

class NotificationSetting extends AbstractModel {
  private $notificationSettingId;
  private $action;
  private $objectId;
  private $notification;
  private $userId;
  private $receiver;
  private $isActive;
  
  function __construct($notificationSettingId, $action, $objectId, $notification, $userId, $receiver, $isActive) {
    $this->notificationSettingId = $notificationSettingId;
    $this->action = $action;
    $this->objectId = $objectId;
    $this->notification = $notification;
    $this->userId = $userId;
    $this->receiver = $receiver;
    $this->isActive = $isActive;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['notificationSettingId'] = $this->notificationSettingId;
    $dict['action'] = $this->action;
    $dict['objectId'] = $this->objectId;
    $dict['notification'] = $this->notification;
    $dict['userId'] = $this->userId;
    $dict['receiver'] = $this->receiver;
    $dict['isActive'] = $this->isActive;
    
    return $dict;
  }
  
  static function getFeatures() {
    $dict = array();
    $dict['notificationSettingId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "notificationSettingId", "public" => False];
    $dict['action'] = ['read_only' => False, "type" => "str(50)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "action", "public" => False];
    $dict['objectId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "objectId", "public" => False];
    $dict['notification'] = ['read_only' => False, "type" => "str(50)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "notification", "public" => False];
    $dict['userId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "userId", "public" => False];
    $dict['receiver'] = ['read_only' => False, "type" => "str(256)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "receiver", "public" => False];
    $dict['isActive'] = ['read_only' => False, "type" => "bool", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "isActive", "public" => False];

    return $dict;
  }

  function getPrimaryKey() {
    return "notificationSettingId";
  }
  
  function getPrimaryKeyValue() {
    return $this->notificationSettingId;
  }
  
  function getId() {
    return $this->notificationSettingId;
  }
  
  function setId($id) {
    $this->notificationSettingId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose() {
    return get_object_vars($this);
  }
  
  function getAction() {
    return $this->action;
  }
  
  function setAction($action) {
    $this->action = $action;
  }
  
  function getObjectId() {
    return $this->objectId;
  }
  
  function setObjectId($objectId) {
    $this->objectId = $objectId;
  }
  
  function getNotification() {
    return $this->notification;
  }
  
  function setNotification($notification) {
    $this->notification = $notification;
  }
  
  function getUserId() {
    return $this->userId;
  }
  
  function setUserId($userId) {
    $this->userId = $userId;
  }
  
  function getReceiver() {
    return $this->receiver;
  }
  
  function setReceiver($receiver) {
    $this->receiver = $receiver;
  }
  
  function getIsActive() {
    return $this->isActive;
  }
  
  function setIsActive($isActive) {
    $this->isActive = $isActive;
  }
  
  const NOTIFICATION_SETTING_ID = "notificationSettingId";
  const ACTION = "action";
  const OBJECT_ID = "objectId";
  const NOTIFICATION = "notification";
  const USER_ID = "userId";
  const RECEIVER = "receiver";
  const IS_ACTIVE = "isActive";

  const PERM_CREATE = "permNotificationSettingCreate";
  const PERM_READ = "permNotificationSettingRead";
  const PERM_UPDATE = "permNotificationSettingUpdate";
  const PERM_DELETE = "permNotificationSettingDelete";
}
