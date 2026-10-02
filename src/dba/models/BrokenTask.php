<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModel;

class BrokenTask extends AbstractModel {
  private ?int $brokenTaskId;
  private ?int $taskId;
  private ?int $time;
  private ?string $reason;
  
  function __construct(?int $brokenTaskId, ?int $taskId, ?int $time, ?string $reason) {
    $this->brokenTaskId = $brokenTaskId;
    $this->taskId = $taskId;
    $this->time = $time;
    $this->reason = $reason;
  }
  
  function getKeyValueDict(): array {
    $dict = array();
    $dict['brokenTaskId'] = $this->brokenTaskId;
    $dict['taskId'] = $this->taskId;
    $dict['time'] = $this->time;
    $dict['reason'] = $this->reason;
    
    return $dict;
  }
  
  static function getFeatures(): array {
    $dict = array();
    $dict['brokenTaskId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "brokenTaskId", "public" => False, "dba_mapping" => False];
    $dict['taskId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "taskId", "public" => False, "dba_mapping" => False];
    $dict['time'] = ['read_only' => True, "type" => "int64", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "time", "public" => False, "dba_mapping" => False];
    $dict['reason'] = ['read_only' => True, "type" => "str(256)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "reason", "public" => False, "dba_mapping" => False];

    return $dict;
  }

  function getPrimaryKey(): string {
    return "brokenTaskId";
  }
  
  function getPrimaryKeyValue(): ?int {
    return $this->brokenTaskId;
  }
  
  function getId(): ?int {
    return $this->brokenTaskId;
  }
  
  function setId($id): void {
    $this->brokenTaskId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose(): array {
    return get_object_vars($this);
  }
  
  function getTaskId(): ?int {
    return $this->taskId;
  }
  
  function setTaskId(?int $taskId): void {
    $this->taskId = $taskId;
  }
  
  function getTime(): ?int {
    return $this->time;
  }
  
  function setTime(?int $time): void {
    $this->time = $time;
  }
  
  function getReason(): ?string {
    return $this->reason;
  }
  
  function setReason(?string $reason): void {
    $this->reason = $reason;
  }
  
  const BROKEN_TASK_ID = "brokenTaskId";
  const TASK_ID = "taskId";
  const TIME = "time";
  const REASON = "reason";

  const PERM_CREATE = "permBrokenTaskCreate";
  const PERM_READ = "permBrokenTaskRead";
  const PERM_UPDATE = "permBrokenTaskUpdate";
  const PERM_DELETE = "permBrokenTaskDelete";
}
