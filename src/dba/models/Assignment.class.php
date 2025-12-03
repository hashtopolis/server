<?php

namespace DBA;

class Assignment extends AbstractModel {
  private ?int $assignmentId;
  private ?int $taskId;
  private ?int $agentId;
  private ?string $benchmark;
  
  function __construct(?int $assignmentId, ?int $taskId, ?int $agentId, ?string $benchmark) {
    $this->assignmentId = $assignmentId;
    $this->taskId = $taskId;
    $this->agentId = $agentId;
    $this->benchmark = $benchmark;
  }
  
  function getKeyValueDict(): array {
    $dict = array();
    $dict['assignmentId'] = $this->assignmentId;
    $dict['taskId'] = $this->taskId;
    $dict['agentId'] = $this->agentId;
    $dict['benchmark'] = $this->benchmark;
    
    return $dict;
  }
  
  static function getFeatures(): array {
    $dict = array();
    $dict['assignmentId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "assignmentId", "public" => False, "dba_mapping" => False];
    $dict['taskId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "taskId", "public" => False, "dba_mapping" => False];
    $dict['agentId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "agentId", "public" => False, "dba_mapping" => False];
    $dict['benchmark'] = ['read_only' => False, "type" => "str(50)", "subtype" => "unset", "choices" => "unset", "null" => True, "pk" => False, "protected" => False, "private" => False, "alias" => "benchmark", "public" => False, "dba_mapping" => False];

    return $dict;
  }

  function getPrimaryKey(): string {
    return "assignmentId";
  }
  
  function getPrimaryKeyValue(): ?int {
    return $this->assignmentId;
  }
  
  function getId(): ?int {
    return $this->assignmentId;
  }
  
  function setId($id): void {
    $this->assignmentId = $id;
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
  
  function getAgentId(): ?int {
    return $this->agentId;
  }
  
  function setAgentId(?int $agentId): void {
    $this->agentId = $agentId;
  }
  
  function getBenchmark(): ?string {
    return $this->benchmark;
  }
  
  function setBenchmark(?string $benchmark): void {
    $this->benchmark = $benchmark;
  }
  
  const ASSIGNMENT_ID = "assignmentId";
  const TASK_ID = "taskId";
  const AGENT_ID = "agentId";
  const BENCHMARK = "benchmark";

  const PERM_CREATE = "permAgentAssignmentCreate";
  const PERM_READ = "permAgentAssignmentRead";
  const PERM_UPDATE = "permAgentAssignmentUpdate";
  const PERM_DELETE = "permAgentAssignmentDelete";
}
