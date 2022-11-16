<?php

namespace DBA;

class CrackerBinaryType extends AbstractModel {
  private $crackerBinaryTypeId;
  private $typeName;
  private $isChunkingAvailable;
  
  function __construct($crackerBinaryTypeId, $typeName, $isChunkingAvailable) {
    $this->crackerBinaryTypeId = $crackerBinaryTypeId;
    $this->typeName = $typeName;
    $this->isChunkingAvailable = $isChunkingAvailable;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['crackerBinaryTypeId'] = $this->crackerBinaryTypeId;
    $dict['typeName'] = $this->typeName;
    $dict['isChunkingAvailable'] = $this->isChunkingAvailable;
    
    return $dict;
  }
  
  static function getFeatures() {
    $dict = array();
    $dict['crackerBinaryTypeId'] = ['read_only' => True, "type" => "int", "null" => False, "pk" => True];
    $dict['typeName'] = ['read_only' => False, "type" => "str(30)", "null" => False, "pk" => False];
    $dict['isChunkingAvailable'] = ['read_only' => False, "type" => "bool", "null" => False, "pk" => False];

    return $dict;
  }

  function getPrimaryKey() {
    return "crackerBinaryTypeId";
  }
  
  function getPrimaryKeyValue() {
    return $this->crackerBinaryTypeId;
  }
  
  function getId() {
    return $this->crackerBinaryTypeId;
  }
  
  function setId($id) {
    $this->crackerBinaryTypeId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose() {
    return get_object_vars($this);
  }
  
  function getTypeName() {
    return $this->typeName;
  }
  
  function setTypeName($typeName) {
    $this->typeName = $typeName;
  }
  
  function getIsChunkingAvailable() {
    return $this->isChunkingAvailable;
  }
  
  function setIsChunkingAvailable($isChunkingAvailable) {
    $this->isChunkingAvailable = $isChunkingAvailable;
  }
  
  const CRACKER_BINARY_TYPE_ID = "crackerBinaryTypeId";
  const TYPE_NAME = "typeName";
  const IS_CHUNKING_AVAILABLE = "isChunkingAvailable";
}
