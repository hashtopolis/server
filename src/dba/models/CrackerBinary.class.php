<?php

namespace DBA;

class CrackerBinary extends AbstractModel {
  private $crackerBinaryId;
  private $crackerBinaryTypeId;
  private $version;
  private $platform;
  
  function __construct($crackerBinaryId, $crackerBinaryTypeId, $version, $platform) {
    $this->crackerBinaryId = $crackerBinaryId;
    $this->crackerBinaryTypeId = $crackerBinaryTypeId;
    $this->version = $version;
    $this->platform = $platform;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['crackerBinaryId'] = $this->crackerBinaryId;
    $dict['crackerBinaryTypeId'] = $this->crackerBinaryTypeId;
    $dict['version'] = $this->version;
    $dict['platform'] = $this->platform;
    
    return $dict;
  }
  
  function getPrimaryKey() {
    return "crackerBinaryId";
  }
  
  function getPrimaryKeyValue() {
    return $this->crackerBinaryId;
  }
  
  function getId() {
    return $this->crackerBinaryId;
  }
  
  function setId($id) {
    $this->crackerBinaryId = $id;
  }
  
  function getCrackerBinaryTypeId(){
    return $this->crackerBinaryTypeId;
  }
  
  function setCrackerBinaryTypeId($crackerBinaryTypeId){
    $this->crackerBinaryTypeId = $crackerBinaryTypeId;
  }
  
  function getVersion(){
    return $this->version;
  }
  
  function setVersion($version){
    $this->version = $version;
  }
  
  function getPlatform(){
    return $this->platform;
  }
  
  function setPlatform($platform){
    $this->platform = $platform;
  }

  const CRACKER_BINARY_ID = "crackerBinaryId";
  const CRACKER_BINARY_TYPE_ID = "crackerBinaryTypeId";
  const VERSION = "version";
  const PLATFORM = "platform";
}
