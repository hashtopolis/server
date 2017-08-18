<?php

namespace DBA;

class BinaryType extends AbstractModel {
  private $binaryTypeId;
  private $typeName;
  private $isChunkingAvailable;
  
  function __construct($binaryTypeId, $typeName, $isChunkingAvailable) {
    $this->binaryTypeId = $binaryTypeId;
    $this->typeName = $typeName;
    $this->isChunkingAvailable = $isChunkingAvailable;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['binaryTypeId'] = $this->binaryTypeId;
    $dict['typeName'] = $this->typeName;
    $dict['isChunkingAvailable'] = $this->isChunkingAvailable;
    
    return $dict;
  }
  
  function getPrimaryKey() {
    return "binaryTypeId";
  }
  
  function getPrimaryKeyValue() {
    return $this->binaryTypeId;
  }
  
  function getId() {
    return $this->binaryTypeId;
  }
  
  function setId($id) {
    $this->binaryTypeId = $id;
  }
  
  function getTypeName(){
    return $this->typeName;
  }
  
  function setTypeName($typeName){
    $this->typeName = $typeName;
  }
  
  function getIsChunkingAvailable(){
    return $this->isChunkingAvailable;
  }
  
  function setIsChunkingAvailable($isChunkingAvailable){
    $this->isChunkingAvailable = $isChunkingAvailable;
  }

  const BINARY_TYPE_ID = "binaryTypeId";
  const TYPE_NAME = "typeName";
  const IS_CHUNKING_AVAILABLE = "isChunkingAvailable";
}
