<?php

namespace DBA;

class Supertask extends AbstractModel {
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
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose() {
    return get_object_vars($this);
  }
  
  function getSupertaskName() {
    return $this->supertaskName;
  }
  
  function setSupertaskName($supertaskName) {
    $this->supertaskName = $supertaskName;
  }
  
  const SUPERTASK_ID = "supertaskId";
  const SUPERTASK_NAME = "supertaskName";
}
