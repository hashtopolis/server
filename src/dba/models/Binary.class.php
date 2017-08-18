<?php

namespace DBA;

class Binary extends AbstractModel {
  private $binaryId;
  private $binaryTypeId;
  private $version;
  private $platform;
  
  function __construct($binaryId, $binaryTypeId, $version, $platform) {
    $this->binaryId = $binaryId;
    $this->binaryTypeId = $binaryTypeId;
    $this->version = $version;
    $this->platform = $platform;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['binaryId'] = $this->binaryId;
    $dict['binaryTypeId'] = $this->binaryTypeId;
    $dict['version'] = $this->version;
    $dict['platform'] = $this->platform;
    
    return $dict;
  }
  
  function getPrimaryKey() {
    return "binaryId";
  }
  
  function getPrimaryKeyValue() {
    return $this->binaryId;
  }
  
  function getId() {
    return $this->binaryId;
  }
  
  function setId($id) {
    $this->binaryId = $id;
  }
  
  function getBinaryTypeId(){
    return $this->binaryTypeId;
  }
  
  function setBinaryTypeId($binaryTypeId){
    $this->binaryTypeId = $binaryTypeId;
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

  const BINARY_ID = "binaryId";
  const BINARY_TYPE_ID = "binaryTypeId";
  const VERSION = "version";
  const PLATFORM = "platform";
}
