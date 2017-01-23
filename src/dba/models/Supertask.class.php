<?php

/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 02.01.17
 * Time: 23:57
 */

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
  
  function getSupertaskName(){
    return $this->supertaskName;
  }
  
  function setSupertaskName($supertaskName){
    $this->supertaskName = $supertaskName;
  }

  const SUPERTASK_ID = "supertaskId";
  const SUPERTASK_NAME = "supertaskName";
}
