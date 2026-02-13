<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModel;

class Session extends AbstractModel {
  private ?int $sessionId;
  private ?int $userId;
  private ?int $sessionStartDate;
  private ?int $lastActionDate;
  private ?int $isOpen;
  private ?int $sessionLifetime;
  private ?string $sessionKey;
  
  function __construct(?int $sessionId, ?int $userId, ?int $sessionStartDate, ?int $lastActionDate, ?int $isOpen, ?int $sessionLifetime, ?string $sessionKey) {
    $this->sessionId = $sessionId;
    $this->userId = $userId;
    $this->sessionStartDate = $sessionStartDate;
    $this->lastActionDate = $lastActionDate;
    $this->isOpen = $isOpen;
    $this->sessionLifetime = $sessionLifetime;
    $this->sessionKey = $sessionKey;
  }
  
  function getKeyValueDict(): array {
    $dict = array();
    $dict['sessionId'] = $this->sessionId;
    $dict['userId'] = $this->userId;
    $dict['sessionStartDate'] = $this->sessionStartDate;
    $dict['lastActionDate'] = $this->lastActionDate;
    $dict['isOpen'] = $this->isOpen;
    $dict['sessionLifetime'] = $this->sessionLifetime;
    $dict['sessionKey'] = $this->sessionKey;
    
    return $dict;
  }
  
  static function getFeatures(): array {
    $dict = array();
    $dict['sessionId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "sessionId", "public" => False, "dba_mapping" => False];
    $dict['userId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "userId", "public" => False, "dba_mapping" => False];
    $dict['sessionStartDate'] = ['read_only' => True, "type" => "int64", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "sessionStartDate", "public" => False, "dba_mapping" => False];
    $dict['lastActionDate'] = ['read_only' => True, "type" => "int64", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "lastActionDate", "public" => False, "dba_mapping" => False];
    $dict['isOpen'] = ['read_only' => True, "type" => "bool", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "isOpen", "public" => False, "dba_mapping" => False];
    $dict['sessionLifetime'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "sessionLifetime", "public" => False, "dba_mapping" => False];
    $dict['sessionKey'] = ['read_only' => True, "type" => "str(256)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "sessionKey", "public" => False, "dba_mapping" => False];

    return $dict;
  }

  function getPrimaryKey(): string {
    return "sessionId";
  }
  
  function getPrimaryKeyValue(): ?int {
    return $this->sessionId;
  }
  
  function getId(): ?int {
    return $this->sessionId;
  }
  
  function setId($id): void {
    $this->sessionId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose(): array {
    return get_object_vars($this);
  }
  
  function getUserId(): ?int {
    return $this->userId;
  }
  
  function setUserId(?int $userId): void {
    $this->userId = $userId;
  }
  
  function getSessionStartDate(): ?int {
    return $this->sessionStartDate;
  }
  
  function setSessionStartDate(?int $sessionStartDate): void {
    $this->sessionStartDate = $sessionStartDate;
  }
  
  function getLastActionDate(): ?int {
    return $this->lastActionDate;
  }
  
  function setLastActionDate(?int $lastActionDate): void {
    $this->lastActionDate = $lastActionDate;
  }
  
  function getIsOpen(): ?int {
    return $this->isOpen;
  }
  
  function setIsOpen(?int $isOpen): void {
    $this->isOpen = $isOpen;
  }
  
  function getSessionLifetime(): ?int {
    return $this->sessionLifetime;
  }
  
  function setSessionLifetime(?int $sessionLifetime): void {
    $this->sessionLifetime = $sessionLifetime;
  }
  
  function getSessionKey(): ?string {
    return $this->sessionKey;
  }
  
  function setSessionKey(?string $sessionKey): void {
    $this->sessionKey = $sessionKey;
  }
  
  const SESSION_ID = "sessionId";
  const USER_ID = "userId";
  const SESSION_START_DATE = "sessionStartDate";
  const LAST_ACTION_DATE = "lastActionDate";
  const IS_OPEN = "isOpen";
  const SESSION_LIFETIME = "sessionLifetime";
  const SESSION_KEY = "sessionKey";

  const PERM_CREATE = "permSessionCreate";
  const PERM_READ = "permSessionRead";
  const PERM_UPDATE = "permSessionUpdate";
  const PERM_DELETE = "permSessionDelete";
}
