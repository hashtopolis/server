<?php

namespace DBA;

class CrackerBinary extends AbstractModel {
  private $crackerBinaryId;
  private $crackerBinaryTypeId;
  private $version;
  private $downloadUrl;
  private $binaryName;
  
  function __construct($crackerBinaryId, $crackerBinaryTypeId, $version, $downloadUrl, $binaryName) {
    $this->crackerBinaryId = $crackerBinaryId;
    $this->crackerBinaryTypeId = $crackerBinaryTypeId;
    $this->version = $version;
    $this->downloadUrl = $downloadUrl;
    $this->binaryName = $binaryName;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['crackerBinaryId'] = $this->crackerBinaryId;
    $dict['crackerBinaryTypeId'] = $this->crackerBinaryTypeId;
    $dict['version'] = $this->version;
    $dict['downloadUrl'] = $this->downloadUrl;
    $dict['binaryName'] = $this->binaryName;
    
    return $dict;
  }
  
  static function getFeatures() {
    $dict = array();
    $dict['crackerBinaryId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "crackerBinaryId"];
    $dict['crackerBinaryTypeId'] = ['read_only' => False, "type" => "int", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "crackerBinaryTypeId"];
    $dict['version'] = ['read_only' => False, "type" => "str(20)", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "version"];
    $dict['downloadUrl'] = ['read_only' => False, "type" => "str(150)", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "downloadUrl"];
    $dict['binaryName'] = ['read_only' => False, "type" => "str(50)", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "binaryName"];

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
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose() {
    return get_object_vars($this);
  }
  
  function getCrackerBinaryTypeId() {
    return $this->crackerBinaryTypeId;
  }
  
  function setCrackerBinaryTypeId($crackerBinaryTypeId) {
    $this->crackerBinaryTypeId = $crackerBinaryTypeId;
  }
  
  function getVersion() {
    return $this->version;
  }
  
  function setVersion($version) {
    $this->version = $version;
  }
  
  function getDownloadUrl() {
    return $this->downloadUrl;
  }
  
  function setDownloadUrl($downloadUrl) {
    $this->downloadUrl = $downloadUrl;
  }
  
  function getBinaryName() {
    return $this->binaryName;
  }
  
  function setBinaryName($binaryName) {
    $this->binaryName = $binaryName;
  }
  
  const CRACKER_BINARY_ID = "crackerBinaryId";
  const CRACKER_BINARY_TYPE_ID = "crackerBinaryTypeId";
  const VERSION = "version";
  const DOWNLOAD_URL = "downloadUrl";
  const BINARY_NAME = "binaryName";
}
