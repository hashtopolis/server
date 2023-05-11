<?php

namespace DBA;

class Assignment extends AbstractModel {
  private $assignmentId;
  private $taskId;
  private $agentId;
  private $benchmark;
  
  function __construct($assignmentId, $taskId, $agentId, $benchmark) {
    $this->assignmentId = $assignmentId;
    $this->taskId = $taskId;
    $this->agentId = $agentId;
    $this->benchmark = $benchmark;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['assignmentId'] = $this->assignmentId;
    $dict['taskId'] = $this->taskId;
    $dict['agentId'] = $this->agentId;
    $dict['benchmark'] = $this->benchmark;
    
    return $dict;
  }
  
  static function getFeatures() {
    $dict = array();
    $dict['assignmentId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "assignmentId"];
    $dict['taskId'] = ['read_only' => False, "type" => "int", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "taskId"];
    $dict['agentId'] = ['read_only' => False, "type" => "int", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "agentId"];
    $dict['benchmark'] = ['read_only' => False, "type" => "str(50)", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "benchmark"];

    return $dict;
  }

  function getPrimaryKey() {
    return "assignmentId";
  }
  
  function getPrimaryKeyValue() {
    return $this->assignmentId;
  }
  
  function getId() {
    return $this->assignmentId;
  }
  
  function setId($id) {
    $this->assignmentId = $id;
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
  
  function getAgentId() {
    return $this->agentId;
  }
  
  function setAgentId($agentId) {
    $this->agentId = $agentId;
  }
  
  function getBenchmark() {
    return $this->benchmark;
  }
  
  function setBenchmark($benchmark) {
    $this->benchmark = $benchmark;
  }
  
  const ASSIGNMENT_ID = "assignmentId";
  const TASK_ID = "taskId";
  const AGENT_ID = "agentId";
  const BENCHMARK = "benchmark";
}
