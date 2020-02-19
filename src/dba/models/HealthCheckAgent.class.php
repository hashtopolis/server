<?php

namespace DBA;

class HealthCheckAgent extends AbstractModel {
  private $healthCheckAgentId;
  private $healthCheckId;
  private $agentId;
  private $status;
  private $cracked;
  private $numGpus;
  private $start;
  private $end;
  private $errors;
  
  function __construct($healthCheckAgentId, $healthCheckId, $agentId, $status, $cracked, $numGpus, $start, $end, $errors) {
    $this->healthCheckAgentId = $healthCheckAgentId;
    $this->healthCheckId = $healthCheckId;
    $this->agentId = $agentId;
    $this->status = $status;
    $this->cracked = $cracked;
    $this->numGpus = $numGpus;
    $this->start = $start;
    $this->end = $end;
    $this->errors = $errors;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['healthCheckAgentId'] = $this->healthCheckAgentId;
    $dict['healthCheckId'] = $this->healthCheckId;
    $dict['agentId'] = $this->agentId;
    $dict['status'] = $this->status;
    $dict['cracked'] = $this->cracked;
    $dict['numGpus'] = $this->numGpus;
    $dict['start'] = $this->start;
    $dict['end'] = $this->end;
    $dict['errors'] = $this->errors;
    
    return $dict;
  }
  
  function getPrimaryKey() {
    return "healthCheckAgentId";
  }
  
  function getPrimaryKeyValue() {
    return $this->healthCheckAgentId;
  }
  
  function getId() {
    return $this->healthCheckAgentId;
  }
  
  function setId($id) {
    $this->healthCheckAgentId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose() {
    return get_object_vars($this);
  }
  
  function getHealthCheckId() {
    return $this->healthCheckId;
  }
  
  function setHealthCheckId($healthCheckId) {
    $this->healthCheckId = $healthCheckId;
  }
  
  function getAgentId() {
    return $this->agentId;
  }
  
  function setAgentId($agentId) {
    $this->agentId = $agentId;
  }
  
  function getStatus() {
    return $this->status;
  }
  
  function setStatus($status) {
    $this->status = $status;
  }
  
  function getCracked() {
    return $this->cracked;
  }
  
  function setCracked($cracked) {
    $this->cracked = $cracked;
  }
  
  function getNumGpus() {
    return $this->numGpus;
  }
  
  function setNumGpus($numGpus) {
    $this->numGpus = $numGpus;
  }
  
  function getStart() {
    return $this->start;
  }
  
  function setStart($start) {
    $this->start = $start;
  }
  
  function getEnd() {
    return $this->end;
  }
  
  function setEnd($end) {
    $this->end = $end;
  }
  
  function getErrors() {
    return $this->errors;
  }
  
  function setErrors($errors) {
    $this->errors = $errors;
  }
  
  const HEALTH_CHECK_AGENT_ID = "healthCheckAgentId";
  const HEALTH_CHECK_ID = "healthCheckId";
  const AGENT_ID = "agentId";
  const STATUS = "status";
  const CRACKED = "cracked";
  const NUM_GPUS = "numGpus";
  const START = "start";
  const END = "end";
  const ERRORS = "errors";
}
