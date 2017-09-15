<?php

namespace DBA;

class CrackerBinary extends AbstractModel {
  private $crackerBinaryId;
  private $crackerBinaryTypeId;
  private $version;
  private $platform;
  private $downloadUrl;
  private $binaryName;
  
  function __construct($crackerBinaryId, $crackerBinaryTypeId, $version, $platform, $downloadUrl, $binaryName) {
    $this->crackerBinaryId = $crackerBinaryId;
    $this->crackerBinaryTypeId = $crackerBinaryTypeId;
    $this->version = $version;
    $this->platform = $platform;
    $this->downloadUrl = $downloadUrl;
    $this->binaryName = $binaryName;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['crackerBinaryId'] = $this->crackerBinaryId;
    $dict['crackerBinaryTypeId'] = $this->crackerBinaryTypeId;
    $dict['version'] = $this->version;
    $dict['platform'] = $this->platform;
    $dict['downloadUrl'] = $this->downloadUrl;
    $dict['binaryName'] = $this->binaryName;
    
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
  
  function getDownloadUrl(){
    return $this->downloadUrl;
  }
  
  function setDownloadUrl($downloadUrl){
    $this->downloadUrl = $downloadUrl;
  }
  
  function getBinaryName(){
    return $this->binaryName;
  }
  
  function setBinaryName($binaryName){
    $this->binaryName = $binaryName;
  }

  const CRACKER_BINARY_ID = "crackerBinaryId";
  const CRACKER_BINARY_TYPE_ID = "crackerBinaryTypeId";
  const VERSION = "version";
  const PLATFORM = "platform";
  const DOWNLOAD_URL = "downloadUrl";
  const BINARY_NAME = "binaryName";
}
