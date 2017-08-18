<?php

namespace DBA;

class AgentStat extends AbstractModel {
  private $agentStatId;
  private $statType;
  private $time;
  private $value;
  
  function __construct($agentStatId, $statType, $time, $value) {
    $this->agentStatId = $agentStatId;
    $this->statType = $statType;
    $this->time = $time;
    $this->value = $value;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['agentStatId'] = $this->agentStatId;
    $dict['statType'] = $this->statType;
    $dict['time'] = $this->time;
    $dict['value'] = $this->value;
    
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
  
  function getStatType(){
    return $this->statType;
  }
  
  function setStatType($statType){
    $this->statType = $statType;
  }
  
  function getTime(){
    return $this->time;
  }
  
  function setTime($time){
    $this->time = $time;
  }
  
  function getValue(){
    return $this->value;
  }
  
  function setValue($value){
    $this->value = $value;
  }

  const AGENT_STAT_ID = "agentStatId";
  const STAT_TYPE = "statType";
  const TIME = "time";
  const VALUE = "value";
}
