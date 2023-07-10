<?php

namespace DBA;

class Benchmark extends AbstractModel {
  private $benchmarkId;
  private $benchmarkType;
  private $benchmarkValue;
  private $attackParameters;
  private $hashMode;
  private $hardwareGroupId;
  private $ttl;
  
  function __construct($benchmarkId, $benchmarkType, $benchmarkValue, $attackParameters, $hashMode, $hardwareGroupId, $ttl) {
    $this->benchmarkId = $benchmarkId;
    $this->benchmarkType = $benchmarkType;
    $this->benchmarkValue = $benchmarkValue;
    $this->attackParameters = $attackParameters;
    $this->hashMode = $hashMode;
    $this->hardwareGroupId = $hardwareGroupId;
    $this->ttl = $ttl;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['benchmarkId'] = $this->benchmarkId;
    $dict['benchmarkType'] = $this->benchmarkType;
    $dict['benchmarkValue'] = $this->benchmarkValue;
    $dict['attackParameters'] = $this->attackParameters;
    $dict['hashMode'] = $this->hashMode;
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
  
  function setId($id) {
    $this->benchmarkId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose() {
    return get_object_vars($this);
  }
  
  function getBenchmarkType() {
    return $this->benchmarkType;
  }
  
  function setBenchmarkType($benchmarkType) {
    $this->benchmarkType = $benchmarkType;
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
  
  function getHashMode() {
    return $this->hashMode;
  }
  
  function setHashMode($hashMode) {
    $this->hashMode = $hashMode;
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
  
  const BENCHMARK_ID = "benchmarkId";
  const BENCHMARK_TYPE = "benchmarkType";
  const BENCHMARK_VALUE = "benchmarkValue";
  const ATTACK_PARAMETERS = "attackParameters";
  const HASH_MODE = "hashMode";
  const HARDWARE_GROUP_ID = "hardwareGroupId";
  const TTL = "ttl";
}
