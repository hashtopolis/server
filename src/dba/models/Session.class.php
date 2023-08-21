<?php

namespace DBA;

class Session extends AbstractModel {
  private $sessionId;
  private $userId;
  private $sessionStartDate;
  private $lastActionDate;
  private $isOpen;
  private $sessionLifetime;
  private $sessionKey;
  
  function __construct($sessionId, $userId, $sessionStartDate, $lastActionDate, $isOpen, $sessionLifetime, $sessionKey) {
    $this->sessionId = $sessionId;
    $this->userId = $userId;
    $this->sessionStartDate = $sessionStartDate;
    $this->lastActionDate = $lastActionDate;
    $this->isOpen = $isOpen;
    $this->sessionLifetime = $sessionLifetime;
    $this->sessionKey = $sessionKey;
  }
  
  function getKeyValueDict() {
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
  
  static function getFeatures() {
    $dict = array();
    $dict['sessionId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "sessionId"];
    $dict['userId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "userId"];
    $dict['sessionStartDate'] = ['read_only' => True, "type" => "int64", "subtype" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "sessionStartDate"];
    $dict['lastActionDate'] = ['read_only' => True, "type" => "int64", "subtype" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "lastActionDate"];
    $dict['isOpen'] = ['read_only' => True, "type" => "bool", "subtype" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "isOpen"];
    $dict['sessionLifetime'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "sessionLifetime"];
    $dict['sessionKey'] = ['read_only' => True, "type" => "str(256)", "subtype" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "sessionKey"];

    return $dict;
  }

  function getPrimaryKey() {
    return "sessionId";
  }
  
  function getPrimaryKeyValue() {
    return $this->sessionId;
  }
  
  function getId() {
    return $this->sessionId;
  }
  
  function setId($id) {
    $this->sessionId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose() {
    return get_object_vars($this);
  }
  
  function getUserId() {
    return $this->userId;
  }
  
  function setUserId($userId) {
    $this->userId = $userId;
  }
  
  function getSessionStartDate() {
    return $this->sessionStartDate;
  }
  
  function setSessionStartDate($sessionStartDate) {
    $this->sessionStartDate = $sessionStartDate;
  }
  
  function getLastActionDate() {
    return $this->lastActionDate;
  }
  
  function setLastActionDate($lastActionDate) {
    $this->lastActionDate = $lastActionDate;
  }
  
  function getIsOpen() {
    return $this->isOpen;
  }
  
  function setIsOpen($isOpen) {
    $this->isOpen = $isOpen;
  }
  
  function getSessionLifetime() {
    return $this->sessionLifetime;
  }
  
  function setSessionLifetime($sessionLifetime) {
    $this->sessionLifetime = $sessionLifetime;
  }
  
  function getSessionKey() {
    return $this->sessionKey;
  }
  
  function setSessionKey($sessionKey) {
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
