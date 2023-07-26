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
    $dict['notificationSettingId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "notificationSettingId"];
    $dict['action'] = ['read_only' => False, "type" => "str(50)", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "action"];
    $dict['objectId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "objectId"];
    $dict['notification'] = ['read_only' => False, "type" => "str(50)", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "notification"];
    $dict['userId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "userId"];
    $dict['receiver'] = ['read_only' => False, "type" => "str(256)", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "receiver"];
    $dict['isActive'] = ['read_only' => False, "type" => "bool", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "isActive"];

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
}
