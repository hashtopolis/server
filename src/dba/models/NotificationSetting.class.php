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
