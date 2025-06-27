<?php

namespace DBA;

class AgentError extends AbstractModel {
  private ?int $agentErrorId;
  private ?int $agentId;
  private ?int $taskId;
  private ?int $chunkId;
  private ?int $time;
  private ?string $error;
  
  function __construct(?int $agentErrorId, ?int $agentId, ?int $taskId, ?int $chunkId, ?int $time, ?string $error) {
    $this->agentErrorId = $agentErrorId;
    $this->agentId = $agentId;
    $this->taskId = $taskId;
    $this->chunkId = $chunkId;
    $this->time = $time;
    $this->error = $error;
  }
  
  function getKeyValueDict(): array {
    $dict = array();
    $dict['agentErrorId'] = $this->agentErrorId;
    $dict['agentId'] = $this->agentId;
    $dict['taskId'] = $this->taskId;
    $dict['chunkId'] = $this->chunkId;
    $dict['time'] = $this->time;
    $dict['error'] = $this->error;
    
    return $dict;
  }
  
  static function getFeatures(): array {
    $dict = array();
    $dict['agentErrorId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "agentErrorId", "public" => False];
    $dict['agentId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "agentId", "public" => False];
    $dict['taskId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "taskId", "public" => False];
    $dict['chunkId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "chunkId", "public" => False];
    $dict['time'] = ['read_only' => True, "type" => "int64", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "time", "public" => False];
    $dict['error'] = ['read_only' => True, "type" => "str(65535)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "error", "public" => False];

    return $dict;
  }

  function getPrimaryKey(): string {
    return "agentErrorId";
  }
  
  function getPrimaryKeyValue(): ?int {
    return $this->agentErrorId;
  }
  
  function getId(): ?int {
    return $this->agentErrorId;
  }
  
  function setId($id): void {
    $this->agentErrorId = $id;
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
  
  function getChunkId(): ?int {
    return $this->chunkId;
  }
  
  function setChunkId(?int $chunkId): void {
    $this->chunkId = $chunkId;
  }
  
  function getTime(): ?int {
    return $this->time;
  }
  
  function setTime(?int $time): void {
    $this->time = $time;
  }
  
  function getError(): ?string {
    return $this->error;
  }
  
  function setError(?string $error): void {
    $this->error = $error;
  }
  
  const AGENT_ERROR_ID = "agentErrorId";
  const AGENT_ID = "agentId";
  const TASK_ID = "taskId";
  const CHUNK_ID = "chunkId";
  const TIME = "time";
  const ERROR = "error";

  const PERM_CREATE = "permAgentErrorCreate";
  const PERM_READ = "permAgentErrorRead";
  const PERM_UPDATE = "permAgentErrorUpdate";
  const PERM_DELETE = "permAgentErrorDelete";
}
