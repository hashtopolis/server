<?php

class Supertask extends AbstractModel {
  private $modelName = "Supertask";
  
  // Modelvariables
  private $supertaskId;
  private $supertaskName;
  
  
  function __construct($supertaskId, $supertaskName) {
    $this->supertaskId = $supertaskId;
    $this->supertaskName = $supertaskName;
    
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['supertaskId'] = $this->supertaskId;
    $dict['supertaskName'] = $this->supertaskName;
    
    return $dict;
  }
  
  function getPrimaryKey() {
    return "supertaskId";
  }
  
  function getPrimaryKeyValue() {
    return $this->supertaskId;
  }
  
  function getId() {
    return $this->supertaskId;
  }
  
  function setId($id) {
    $this->supertaskId = $id;
  }
  
  function getSupertaskName() {
    return $this->supertaskName;
  }
  
  function setSupertaskName($supertaskName) {
    $this->supertaskName = $supertaskName;
  }
}
