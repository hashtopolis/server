<?php

namespace DBA;

class Speed extends AbstractModel {
  private $speedId;
  private $agentId;
  private $taskId;
  private $speed;
  private $time;
  
  function __construct($speedId, $agentId, $taskId, $speed, $time) {
    $this->speedId = $speedId;
    $this->agentId = $agentId;
    $this->taskId = $taskId;
    $this->speed = $speed;
    $this->time = $time;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['speedId'] = $this->speedId;
    $dict['agentId'] = $this->agentId;
    $dict['taskId'] = $this->taskId;
    $dict['speed'] = $this->speed;
    $dict['time'] = $this->time;
    
    return $dict;
  }
  
  static function getFeatures() {
    $dict = array();
    $dict['speedId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "speedId"];
    $dict['agentId'] = ['read_only' => False, "type" => "int", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "agentId"];
    $dict['taskId'] = ['read_only' => False, "type" => "int", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "taskId"];
    $dict['speed'] = ['read_only' => False, "type" => "int64", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "speed"];
    $dict['time'] = ['read_only' => False, "type" => "int64", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "time"];

    return $dict;
  }

  function getPrimaryKey() {
    return "speedId";
  }
  
  function getPrimaryKeyValue() {
    return $this->speedId;
  }
  
  function getId() {
    return $this->speedId;
  }
  
  function setId($id) {
    $this->speedId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose() {
    return get_object_vars($this);
  }
  
  function getAgentId() {
    return $this->agentId;
  }
  
  function setAgentId($agentId) {
    $this->agentId = $agentId;
  }
  
  function getTaskId() {
    return $this->taskId;
  }
  
  function setTaskId($taskId) {
    $this->taskId = $taskId;
  }
  
  function getSpeed() {
    return $this->speed;
  }
  
  function setSpeed($speed) {
    $this->speed = $speed;
  }
  
  function getTime() {
    return $this->time;
  }
  
  function setTime($time) {
    $this->time = $time;
  }
  
  const SPEED_ID = "speedId";
  const AGENT_ID = "agentId";
  const TASK_ID = "taskId";
  const SPEED = "speed";
  const TIME = "time";
}
