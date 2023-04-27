<?php

namespace DBA;

class Benchmark extends AbstractModel {
  private $benchmarkId;
  private $benchmarkValue;
  private $attackParameters;
  private $hardwareGroupId;
  private $ttl;
  
  function __construct($benchmarkId, $benchmarkValue, $attackParameters, $hardwareGroupId, $ttl) {
    $this->benchmarkId = $benchmarkId;
    $this->benchmarkValue = $benchmarkValue;
    $this->attackParameters = $attackParameters;
    $this->hardwareGroupId = $hardwareGroupId;
    $this->ttl = $ttl;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['benchmarkId'] = $this->benchmarkId;
    $dict['benchmarkValue'] = $this->benchmarkValue;
    $dict['attackParameters'] = $this->attackParameters;
    $dict['hardwareGroupId'] = $this->hardwareGroupId;
    $dict['ttl'] = $this->ttl;
    
    return $dict;
  }
  
  function getPrimaryKey() {
    return "benchmarkId";
  }
  
  function getPrimaryKeyValue() {
    return $this->benchmarkId;
  }
  
  function getId() {
    return $this->benchmarkId;
  }
  
  function setId($benchmarkId) {
    $this->benchmarkId = $benchmarkId;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose() {
    return get_object_vars($this);
  }
  
  function getBenchmarkValue() {
    return $this->benchmarkValue;
  }
  
  function setBenchmarkValue($benchmarkValue) {
    $this->benchmarkValue = $benchmarkValue;
  }
  
  function getAttackParameters() {
    return $this->attackParameters;
  }
  
  function setAttackParameters($attackParameters) {
    $this->attackParameters = $attackParameters;
  }
  
  function getHardwareGroupId() {
    return $this->hardwareGroupId;
  }
  
  function setHardwareGroupId($hardwareGroupId) {
    $this->hardwareGroupId = $hardwareGroupId;
  }
  
  function getTtl() {
    return $this->ttl;
  }
  
  function setTtl($ttl) {
    $this->ttl = $ttl;
  }
  
  const benchmarkId = "benchmarkId";
  const BENCHMARK_VALUE = "benchmarkValue";
  const ATTACK_PARAMETERS = "attackParameters";
  const HARDWARE_GROUP_benchmarkId = "hardwareGroupId";
  const TTL = "ttl";
}
