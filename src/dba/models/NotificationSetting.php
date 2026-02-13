<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModel;

class NotificationSetting extends AbstractModel {
  private ?int $notificationSettingId;
  private ?string $action;
  private ?int $objectId;
  private ?string $notification;
  private ?int $userId;
  private ?string $receiver;
  private ?int $isActive;
  
  function __construct(?int $notificationSettingId, ?string $action, ?int $objectId, ?string $notification, ?int $userId, ?string $receiver, ?int $isActive) {
    $this->notificationSettingId = $notificationSettingId;
    $this->action = $action;
    $this->objectId = $objectId;
    $this->notification = $notification;
    $this->userId = $userId;
    $this->receiver = $receiver;
    $this->isActive = $isActive;
  }
  
  function getKeyValueDict(): array {
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
  
  static function getFeatures(): array {
    $dict = array();
    $dict['notificationSettingId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "notificationSettingId", "public" => False, "dba_mapping" => False];
    $dict['action'] = ['read_only' => False, "type" => "str(50)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "action", "public" => False, "dba_mapping" => False];
    $dict['objectId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "objectId", "public" => False, "dba_mapping" => False];
    $dict['notification'] = ['read_only' => False, "type" => "str(50)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "notification", "public" => False, "dba_mapping" => False];
    $dict['userId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "userId", "public" => False, "dba_mapping" => False];
    $dict['receiver'] = ['read_only' => False, "type" => "str(256)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "receiver", "public" => False, "dba_mapping" => False];
    $dict['isActive'] = ['read_only' => False, "type" => "bool", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "isActive", "public" => False, "dba_mapping" => False];

    return $dict;
  }

  function getPrimaryKey(): string {
    return "notificationSettingId";
  }
  
  function getPrimaryKeyValue(): ?int {
    return $this->notificationSettingId;
  }
  
  function getId(): ?int {
    return $this->notificationSettingId;
  }
  
  function setId($id): void {
    $this->notificationSettingId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose(): array {
    return get_object_vars($this);
  }
  
  function getAction(): ?string {
    return $this->action;
  }
  
  function setAction(?string $action): void {
    $this->action = $action;
  }
  
  function getObjectId(): ?int {
    return $this->objectId;
  }
  
  function setObjectId(?int $objectId): void {
    $this->objectId = $objectId;
  }
  
  function getNotification(): ?string {
    return $this->notification;
  }
  
  function setNotification(?string $notification): void {
    $this->notification = $notification;
  }
  
  function getUserId(): ?int {
    return $this->userId;
  }
  
  function setUserId(?int $userId): void {
    $this->userId = $userId;
  }
  
  function getReceiver(): ?string {
    return $this->receiver;
  }
  
  function setReceiver(?string $receiver): void {
    $this->receiver = $receiver;
  }
  
  function getIsActive(): ?int {
    return $this->isActive;
  }
  
  function setIsActive(?int $isActive): void {
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
