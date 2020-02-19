<?php

namespace DBA;

class HashType extends AbstractModel {
  private $hashTypeId;
  private $description;
  private $isSalted;
  private $isSlowHash;
  
  function __construct($hashTypeId, $description, $isSalted, $isSlowHash) {
    $this->hashTypeId = $hashTypeId;
    $this->description = $description;
    $this->isSalted = $isSalted;
    $this->isSlowHash = $isSlowHash;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['hashTypeId'] = $this->hashTypeId;
    $dict['description'] = $this->description;
    $dict['isSalted'] = $this->isSalted;
    $dict['isSlowHash'] = $this->isSlowHash;
    
    return $dict;
  }
  
  function getPrimaryKey() {
    return "hashTypeId";
  }
  
  function getPrimaryKeyValue() {
    return $this->hashTypeId;
  }
  
  function getId() {
    return $this->hashTypeId;
  }
  
  function setId($id) {
    $this->hashTypeId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose() {
    return get_object_vars($this);
  }
  
  function getDescription() {
    return $this->description;
  }
  
  function setDescription($description) {
    $this->description = $description;
  }
  
  function getIsSalted() {
    return $this->isSalted;
  }
  
  function setIsSalted($isSalted) {
    $this->isSalted = $isSalted;
  }
  
  function getIsSlowHash() {
    return $this->isSlowHash;
  }
  
  function setIsSlowHash($isSlowHash) {
    $this->isSlowHash = $isSlowHash;
  }
  
  const HASH_TYPE_ID = "hashTypeId";
  const DESCRIPTION = "description";
  const IS_SALTED = "isSalted";
  const IS_SLOW_HASH = "isSlowHash";
}
