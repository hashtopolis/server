<?php

namespace DBA;

class AgentStat extends AbstractModel {
  private $agentStatId;
  private $agentId;
  private $statType;
  private $time;
  private $value;
  
  function __construct($agentStatId, $agentId, $statType, $time, $value) {
    $this->agentStatId = $agentStatId;
    $this->agentId = $agentId;
    $this->statType = $statType;
    $this->time = $time;
    $this->value = $value;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['agentStatId'] = $this->agentStatId;
    $dict['agentId'] = $this->agentId;
    $dict['statType'] = $this->statType;
    $dict['time'] = $this->time;
    $dict['value'] = $this->value;
    
    return $dict;
  }
  
  static function getFeatures() {
    $dict = array();
    $dict['agentStatId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "agentStatId"];
    $dict['agentId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "agentId"];
    $dict['statType'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "statType"];
    $dict['time'] = ['read_only' => True, "type" => "int64", "subtype" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "time"];
    $dict['value'] = ['read_only' => True, "type" => "array", "subtype" => "int", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "value"];

    return $dict;
  }

  function getPrimaryKey() {
    return "agentStatId";
  }
  
  function getPrimaryKeyValue() {
    return $this->agentStatId;
  }
  
  function getId() {
    return $this->agentStatId;
  }
  
  function setId($id) {
    $this->agentStatId = $id;
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
  
  function getStatType() {
    return $this->statType;
  }
  
  function setStatType($statType) {
    $this->statType = $statType;
  }
  
  function getTime() {
    return $this->time;
  }
  
  function setTime($time) {
    $this->time = $time;
  }
  
  function getValue() {
    return $this->value;
  }
  
  function setValue($value) {
    $this->value = $value;
  }
  
  const AGENT_STAT_ID = "agentStatId";
  const AGENT_ID = "agentId";
  const STAT_TYPE = "statType";
  const TIME = "time";
  const VALUE = "value";

  const PERM_CREATE = "permAgentStatCreate";
  const PERM_READ = "permAgentStatRead";
  const PERM_UPDATE = "permAgentStatUpdate";
  const PERM_DELETE = "permAgentStatDelete";
}
