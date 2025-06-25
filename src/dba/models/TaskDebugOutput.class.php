<?php

namespace DBA;

class TaskDebugOutput extends AbstractModel {
  private $taskDebugOutputId;
  private $taskId;
  private $output;
  
  function __construct($taskDebugOutputId, $taskId, $output) {
    $this->taskDebugOutputId = $taskDebugOutputId;
    $this->taskId = $taskId;
    $this->output = $output;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['taskDebugOutputId'] = $this->taskDebugOutputId;
    $dict['taskId'] = $this->taskId;
    $dict['output'] = $this->output;
    
    return $dict;
  }
  
  static function getFeatures() {
    $dict = array();
    $dict['taskDebugOutputId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "taskDebugOutputId", "public" => False];
    $dict['taskId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "taskId", "public" => False];
    $dict['output'] = ['read_only' => True, "type" => "str(256)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "output", "public" => False];

    return $dict;
  }

  function getPrimaryKey() {
    return "taskDebugOutputId";
  }
  
  function getPrimaryKeyValue() {
    return $this->taskDebugOutputId;
  }
  
  function getId() {
    return $this->taskDebugOutputId;
  }
  
  function setId($id) {
    $this->taskDebugOutputId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose() {
    return get_object_vars($this);
  }
  
  function getTaskId() {
    return $this->taskId;
  }
  
  function setTaskId($taskId) {
    $this->taskId = $taskId;
  }
  
  function getOutput() {
    return $this->output;
  }
  
  function setOutput($output) {
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
