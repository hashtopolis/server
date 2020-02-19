<?php

namespace DBA;

class HealthCheck extends AbstractModel {
  private $healthCheckId;
  private $time;
  private $status;
  private $checkType;
  private $hashtypeId;
  private $crackerBinaryId;
  private $expectedCracks;
  private $attackCmd;
  
  function __construct($healthCheckId, $time, $status, $checkType, $hashtypeId, $crackerBinaryId, $expectedCracks, $attackCmd) {
    $this->healthCheckId = $healthCheckId;
    $this->time = $time;
    $this->status = $status;
    $this->checkType = $checkType;
    $this->hashtypeId = $hashtypeId;
    $this->crackerBinaryId = $crackerBinaryId;
    $this->expectedCracks = $expectedCracks;
    $this->attackCmd = $attackCmd;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['healthCheckId'] = $this->healthCheckId;
    $dict['time'] = $this->time;
    $dict['status'] = $this->status;
    $dict['checkType'] = $this->checkType;
    $dict['hashtypeId'] = $this->hashtypeId;
    $dict['crackerBinaryId'] = $this->crackerBinaryId;
    $dict['expectedCracks'] = $this->expectedCracks;
    $dict['attackCmd'] = $this->attackCmd;
    
    return $dict;
  }
  
  function getPrimaryKey() {
    return "healthCheckId";
  }
  
  function getPrimaryKeyValue() {
    return $this->healthCheckId;
  }
  
  function getId() {
    return $this->healthCheckId;
  }
  
  function setId($id) {
    $this->healthCheckId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose() {
    return get_object_vars($this);
  }
  
  function getTime() {
    return $this->time;
  }
  
  function setTime($time) {
    $this->time = $time;
  }
  
  function getStatus() {
    return $this->status;
  }
  
  function setStatus($status) {
    $this->status = $status;
  }
  
  function getCheckType() {
    return $this->checkType;
  }
  
  function setCheckType($checkType) {
    $this->checkType = $checkType;
  }
  
  function getHashtypeId() {
    return $this->hashtypeId;
  }
  
  function setHashtypeId($hashtypeId) {
    $this->hashtypeId = $hashtypeId;
  }
  
  function getCrackerBinaryId() {
    return $this->crackerBinaryId;
  }
  
  function setCrackerBinaryId($crackerBinaryId) {
    $this->crackerBinaryId = $crackerBinaryId;
  }
  
  function getExpectedCracks() {
    return $this->expectedCracks;
  }
  
  function setExpectedCracks($expectedCracks) {
    $this->expectedCracks = $expectedCracks;
  }
  
  function getAttackCmd() {
    return $this->attackCmd;
  }
  
  function setAttackCmd($attackCmd) {
    $this->attackCmd = $attackCmd;
  }
  
  const HEALTH_CHECK_ID = "healthCheckId";
  const TIME = "time";
  const STATUS = "status";
  const CHECK_TYPE = "checkType";
  const HASHTYPE_ID = "hashtypeId";
  const CRACKER_BINARY_ID = "crackerBinaryId";
  const EXPECTED_CRACKS = "expectedCracks";
  const ATTACK_CMD = "attackCmd";
}
