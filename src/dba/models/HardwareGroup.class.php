<?php

namespace DBA;

class HardwareGroup extends AbstractModel {
  private $hardwareGroupId;
  private $devices;
  
  function __construct($hardwareGroupId, $devices) {
    $this->hardwareGroupId = $hardwareGroupId;
    $this->devices = $devices;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['hardwareGroupId'] = $this->hardwareGroupId;
    $dict['devices'] = $this->devices;
    
    return $dict;
  }
  
  function getPrimaryKey() {
    return "hardwareGroupId";
  }
  
  function getPrimaryKeyValue() {
    return $this->hardwareGroupId;
  }
  
  function getId() {
    return $this->hardwareGroupId;
  }
  
  function setId($id) {
    $this->hardwareGroupId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose() {
    return get_object_vars($this);
  }
  
  function getDevices() {
    return $this->devices;
  }
  
  function setDevices($devices) {
    $this->devices = $devices;
  }
  
  // function getBenchmarkId() {
  //   return $this->benchmarkId;
  // }
  
  // function setBenchmarkId($benchmarkId) {
  //   $this->benchmarkId = $benchmarkId;
  // }
  
  const HARDWARE_GROUP_ID = "hardwareGroupId";
  const DEVICES = "devices";
  // const BENCHMARK_ID = "benchmarkId";
}
