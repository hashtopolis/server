<?php

namespace DBA;

class LogEntry extends AbstractModel {
  private ?int $logEntryId;
  private ?string $issuer;
  private ?string $issuerId;
  private ?string $level;
  private ?string $message;
  private ?int $time;
  
  function __construct(?int $logEntryId, ?string $issuer, ?string $issuerId, ?string $level, ?string $message, ?int $time) {
    $this->logEntryId = $logEntryId;
    $this->issuer = $issuer;
    $this->issuerId = $issuerId;
    $this->level = $level;
    $this->message = $message;
    $this->time = $time;
  }
  
  function getKeyValueDict(): array {
    $dict = array();
    $dict['logEntryId'] = $this->logEntryId;
    $dict['issuer'] = $this->issuer;
    $dict['issuerId'] = $this->issuerId;
    $dict['level'] = $this->level;
    $dict['message'] = $this->message;
    $dict['time'] = $this->time;
    
    return $dict;
  }
  
  static function getFeatures(): array {
    $dict = array();
    $dict['logEntryId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "logEntryId", "public" => False];
    $dict['issuer'] = ['read_only' => True, "type" => "str(50)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "issuer", "public" => False];
    $dict['issuerId'] = ['read_only' => True, "type" => "str(50)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "issuerId", "public" => False];
    $dict['level'] = ['read_only' => True, "type" => "str(50)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "level", "public" => False];
    $dict['message'] = ['read_only' => True, "type" => "str(65535)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "message", "public" => False];
    $dict['time'] = ['read_only' => True, "type" => "int64", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "time", "public" => False];

    return $dict;
  }

  function getPrimaryKey(): string {
    return "logEntryId";
  }
  
  function getPrimaryKeyValue(): ?int {
    return $this->logEntryId;
  }
  
  function getId(): ?int {
    return $this->logEntryId;
  }
  
  function setId($id): void {
    $this->logEntryId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose(): array {
    return get_object_vars($this);
  }
  
  function getIssuer(): ?string {
    return $this->issuer;
  }
  
  function setIssuer(?string $issuer): void {
    $this->issuer = $issuer;
  }
  
  function getIssuerId(): ?string {
    return $this->issuerId;
  }
  
  function setIssuerId(?string $issuerId): void {
    $this->issuerId = $issuerId;
  }
  
  function getLevel(): ?string {
    return $this->level;
  }
  
  function setLevel(?string $level): void {
    $this->level = $level;
  }
  
  function getMessage(): ?string {
    return $this->message;
  }
  
  function setMessage(?string $message): void {
    $this->message = $message;
  }
  
  function getTime(): ?int {
    return $this->time;
  }
  
  function setTime(?int $time): void {
    $this->time = $time;
  }
  
  const LOG_ENTRY_ID = "logEntryId";
  const ISSUER = "issuer";
  const ISSUER_ID = "issuerId";
  const LEVEL = "level";
  const MESSAGE = "message";
  const TIME = "time";

  const PERM_CREATE = "permLogEntryCreate";
  const PERM_READ = "permLogEntryRead";
  const PERM_UPDATE = "permLogEntryUpdate";
  const PERM_DELETE = "permLogEntryDelete";
}
