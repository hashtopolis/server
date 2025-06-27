<?php

namespace DBA;

class Speed extends AbstractModel {
  private ?int $speedId;
  private ?int $agentId;
  private ?int $taskId;
  private ?int $speed;
  private ?int $time;
  
  function __construct(?int $speedId, ?int $agentId, ?int $taskId, ?int $speed, ?int $time) {
    $this->speedId = $speedId;
    $this->agentId = $agentId;
    $this->taskId = $taskId;
    $this->speed = $speed;
    $this->time = $time;
  }
  
  function getKeyValueDict(): array {
    $dict = array();
    $dict['speedId'] = $this->speedId;
    $dict['agentId'] = $this->agentId;
    $dict['taskId'] = $this->taskId;
    $dict['speed'] = $this->speed;
    $dict['time'] = $this->time;
    
    return $dict;
  }
  
  static function getFeatures(): array {
    $dict = array();
    $dict['speedId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "speedId", "public" => False];
    $dict['agentId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "agentId", "public" => False];
    $dict['taskId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "taskId", "public" => False];
    $dict['speed'] = ['read_only' => True, "type" => "int64", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "speed", "public" => False];
    $dict['time'] = ['read_only' => True, "type" => "int64", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "time", "public" => False];

    return $dict;
  }

  function getPrimaryKey(): string {
    return "speedId";
  }
  
  function getPrimaryKeyValue(): ?int {
    return $this->speedId;
  }
  
  function getId(): ?int {
    return $this->speedId;
  }
  
  function setId($id): void {
    $this->speedId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose(): array {
    return get_object_vars($this);
  }
  
  function getAgentId(): ?int {
    return $this->agentId;
  }
  
  function setAgentId(?int $agentId): void {
    $this->agentId = $agentId;
  }
  
  function getTaskId(): ?int {
    return $this->taskId;
  }
  
  function setTaskId(?int $taskId): void {
    $this->taskId = $taskId;
  }
  
  function getSpeed(): ?int {
    return $this->speed;
  }
  
  function setSpeed(?int $speed): void {
    $this->speed = $speed;
  }
  
  function getTime(): ?int {
    return $this->time;
  }
  
  function setTime(?int $time): void {
    $this->time = $time;
  }
  
  const SPEED_ID = "speedId";
  const AGENT_ID = "agentId";
  const TASK_ID = "taskId";
  const SPEED = "speed";
  const TIME = "time";

  const PERM_CREATE = "permSpeedCreate";
  const PERM_READ = "permSpeedRead";
  const PERM_UPDATE = "permSpeedUpdate";
  const PERM_DELETE = "permSpeedDelete";
}
