<?php

class HashType extends AbstractModel {
  private $modelName = "HashType";
  
  // Modelvariables
  private $hashTypeId;
  private $description;
  
  
  function __construct($hashTypeId, $description) {
    $this->hashTypeId = $hashTypeId;
    $this->description = $description;
    
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['hashTypeId'] = $this->hashTypeId;
    $dict['description'] = $this->description;
    
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
  
  function getDescription() {
    return $this->description;
  }
  
  function setDescription($description) {
    $this->description = $description;
  }
}
