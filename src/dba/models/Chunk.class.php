<?php

namespace DBA;

class Chunk extends AbstractModel {
  private $chunkId;
  private $taskId;
  private $skip;
  private $length;
  private $agentId;
  private $dispatchTime;
  private $solveTime;
  private $checkpoint;
  private $progress;
  private $state;
  private $cracked;
  private $speed;
  
  function __construct($chunkId, $taskId, $skip, $length, $agentId, $dispatchTime, $solveTime, $checkpoint, $progress, $state, $cracked, $speed) {
    $this->chunkId = $chunkId;
    $this->taskId = $taskId;
    $this->skip = $skip;
    $this->length = $length;
    $this->agentId = $agentId;
    $this->dispatchTime = $dispatchTime;
    $this->solveTime = $solveTime;
    $this->checkpoint = $checkpoint;
    $this->progress = $progress;
    $this->state = $state;
    $this->cracked = $cracked;
    $this->speed = $speed;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['chunkId'] = $this->chunkId;
    $dict['taskId'] = $this->taskId;
    $dict['skip'] = $this->skip;
    $dict['length'] = $this->length;
    $dict['agentId'] = $this->agentId;
    $dict['dispatchTime'] = $this->dispatchTime;
    $dict['solveTime'] = $this->solveTime;
    $dict['checkpoint'] = $this->checkpoint;
    $dict['progress'] = $this->progress;
    $dict['state'] = $this->state;
    $dict['cracked'] = $this->cracked;
    $dict['speed'] = $this->speed;
    
    return $dict;
  }
  
  static function getFeatures() {
    $dict = array();
    $dict['chunkId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "chunkId"];
    $dict['taskId'] = ['read_only' => False, "type" => "int", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "taskId"];
    $dict['skip'] = ['read_only' => False, "type" => "uint64", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "skip"];
    $dict['length'] = ['read_only' => False, "type" => "uint64", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "length"];
    $dict['agentId'] = ['read_only' => False, "type" => "int", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "agentId"];
    $dict['dispatchTime'] = ['read_only' => False, "type" => "int64", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "dispatchTime"];
    $dict['solveTime'] = ['read_only' => False, "type" => "int64", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "solveTime"];
    $dict['checkpoint'] = ['read_only' => False, "type" => "int64", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "checkpoint"];
    $dict['progress'] = ['read_only' => False, "type" => "int", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "progress"];
    $dict['state'] = ['read_only' => False, "type" => "int", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "state"];
    $dict['cracked'] = ['read_only' => False, "type" => "int", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "cracked"];
    $dict['speed'] = ['read_only' => False, "type" => "int64", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "speed"];

    return $dict;
  }

  function getPrimaryKey() {
    return "chunkId";
  }
  
  function getPrimaryKeyValue() {
    return $this->chunkId;
  }
  
  function getId() {
    return $this->chunkId;
  }
  
  function setId($id) {
    $this->chunkId = $id;
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
  
  function getSkip() {
    return $this->skip;
  }
  
  function setSkip($skip) {
    $this->skip = $skip;
  }
  
  function getLength() {
    return $this->length;
  }
  
  function setLength($length) {
    $this->length = $length;
  }
  
  function getAgentId() {
    return $this->agentId;
  }
  
  function setAgentId($agentId) {
    $this->agentId = $agentId;
  }
  
  function getDispatchTime() {
    return $this->dispatchTime;
  }
  
  function setDispatchTime($dispatchTime) {
    $this->dispatchTime = $dispatchTime;
  }
  
  function getSolveTime() {
    return $this->solveTime;
  }
  
  function setSolveTime($solveTime) {
    $this->solveTime = $solveTime;
  }
  
  function getCheckpoint() {
    return $this->checkpoint;
  }
  
  function setCheckpoint($checkpoint) {
    $this->checkpoint = $checkpoint;
  }
  
  function getProgress() {
    return $this->progress;
  }
  
  function setProgress($progress) {
    $this->progress = $progress;
  }
  
  function getState() {
    return $this->state;
  }
  
  function setState($state) {
    $this->state = $state;
  }
  
  function getCracked() {
    return $this->cracked;
  }
  
  function setCracked($cracked) {
    $this->cracked = $cracked;
  }
  
  function getSpeed() {
    return $this->speed;
  }
  
  function setSpeed($speed) {
    $this->speed = $speed;
  }
  
  const CHUNK_ID = "chunkId";
  const TASK_ID = "taskId";
  const SKIP = "skip";
  const LENGTH = "length";
  const AGENT_ID = "agentId";
  const DISPATCH_TIME = "dispatchTime";
  const SOLVE_TIME = "solveTime";
  const CHECKPOINT = "checkpoint";
  const PROGRESS = "progress";
  const STATE = "state";
  const CRACKED = "cracked";
  const SPEED = "speed";
}
