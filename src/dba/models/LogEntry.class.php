<?php

namespace DBA;

class LogEntry extends AbstractModel {
  private $logEntryId;
  private $issuer;
  private $issuerId;
  private $level;
  private $message;
  private $time;
  
  function __construct($logEntryId, $issuer, $issuerId, $level, $message, $time) {
    $this->logEntryId = $logEntryId;
    $this->issuer = $issuer;
    $this->issuerId = $issuerId;
    $this->level = $level;
    $this->message = $message;
    $this->time = $time;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['logEntryId'] = $this->logEntryId;
    $dict['issuer'] = $this->issuer;
    $dict['issuerId'] = $this->issuerId;
    $dict['level'] = $this->level;
    $dict['message'] = $this->message;
    $dict['time'] = $this->time;
    
    return $dict;
  }
  
  function getPrimaryKey() {
    return "logEntryId";
  }
  
  function getPrimaryKeyValue() {
    return $this->logEntryId;
  }
  
  function getId() {
    return $this->logEntryId;
  }
  
  function setId($id) {
    $this->logEntryId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose() {
    return get_object_vars($this);
  }
  
  function getIssuer() {
    return $this->issuer;
  }
  
  function setIssuer($issuer) {
    $this->issuer = $issuer;
  }
  
  function getIssuerId() {
    return $this->issuerId;
  }
  
  function setIssuerId($issuerId) {
    $this->issuerId = $issuerId;
  }
  
  function getLevel() {
    return $this->level;
  }
  
  function setLevel($level) {
    $this->level = $level;
  }
  
  function getMessage() {
    return $this->message;
  }
  
  function setMessage($message) {
    $this->message = $message;
  }
  
  function getTime() {
    return $this->time;
  }
  
  function setTime($time) {
    $this->time = $time;
  }
  
  const LOG_ENTRY_ID = "logEntryId";
  const ISSUER = "issuer";
  const ISSUER_ID = "issuerId";
  const LEVEL = "level";
  const MESSAGE = "message";
  const TIME = "time";
}
