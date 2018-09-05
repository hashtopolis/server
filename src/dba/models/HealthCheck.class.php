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
  
  function __construct($healthCheckId, $time, $status, $checkType, $hashtypeId, $crackerBinaryId, $expectedCracks) {
    $this->healthCheckId = $healthCheckId;
    $this->time = $time;
    $this->status = $status;
    $this->checkType = $checkType;
    $this->hashtypeId = $hashtypeId;
    $this->crackerBinaryId = $crackerBinaryId;
    $this->expectedCracks = $expectedCracks;
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
  
  function getTime(){
    return $this->time;
  }
  
  function setTime($time){
    $this->time = $time;
  }
  
  function getStatus(){
    return $this->status;
  }
  
  function setStatus($status){
    $this->status = $status;
  }
  
  function getCheckType(){
    return $this->checkType;
  }
  
  function setCheckType($checkType){
    $this->checkType = $checkType;
  }
  
  function getHashtypeId(){
    return $this->hashtypeId;
  }
  
  function setHashtypeId($hashtypeId){
    $this->hashtypeId = $hashtypeId;
  }
  
  function getCrackerBinaryId(){
    return $this->crackerBinaryId;
  }
  
  function setCrackerBinaryId($crackerBinaryId){
    $this->crackerBinaryId = $crackerBinaryId;
  }
  
  function getExpectedCracks(){
    return $this->expectedCracks;
  }
  
  function setExpectedCracks($expectedCracks){
    $this->expectedCracks = $expectedCracks;
  }

  const HEALTH_CHECK_ID = "healthCheckId";
  const TIME = "time";
  const STATUS = "status";
  const CHECK_TYPE = "checkType";
  const HASHTYPE_ID = "hashtypeId";
  const CRACKER_BINARY_ID = "crackerBinaryId";
  const EXPECTED_CRACKS = "expectedCracks";
}
