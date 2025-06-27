<?php

namespace DBA;

class TaskDebugOutput extends AbstractModel {
  private ?int $taskDebugOutputId;
  private ?int $taskId;
  private ?string $output;
  
  function __construct(?int $taskDebugOutputId, ?int $taskId, ?string $output) {
    $this->taskDebugOutputId = $taskDebugOutputId;
    $this->taskId = $taskId;
    $this->output = $output;
  }
  
  function getKeyValueDict(): array {
    $dict = array();
    $dict['taskDebugOutputId'] = $this->taskDebugOutputId;
    $dict['taskId'] = $this->taskId;
    $dict['output'] = $this->output;
    
    return $dict;
  }
  
  static function getFeatures(): array {
    $dict = array();
    $dict['taskDebugOutputId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "taskDebugOutputId", "public" => False];
    $dict['taskId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "taskId", "public" => False];
    $dict['output'] = ['read_only' => True, "type" => "str(256)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "output", "public" => False];

    return $dict;
  }

  function getPrimaryKey(): string {
    return "taskDebugOutputId";
  }
  
  function getPrimaryKeyValue(): ?int {
    return $this->taskDebugOutputId;
  }
  
  function getId(): ?int {
    return $this->taskDebugOutputId;
  }
  
  function setId($id): void {
    $this->taskDebugOutputId = $id;
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
  
  function getOutput(): ?string {
    return $this->output;
  }
  
  function setOutput(?string $output): void {
    $this->output = $output;
  }
  
  const TASK_DEBUG_OUTPUT_ID = "taskDebugOutputId";
  const TASK_ID = "taskId";
  const OUTPUT = "output";

  const PERM_CREATE = "permTaskDebugOutputCreate";
  const PERM_READ = "permTaskDebugOutputRead";
  const PERM_UPDATE = "permTaskDebugOutputUpdate";
  const PERM_DELETE = "permTaskDebugOutputDelete";
}
