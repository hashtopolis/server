<?php

/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 02.01.17
 * Time: 23:57
 */

namespace DBA;

class HashType extends AbstractModel {
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
  
  function getDescription(){
    return $this->description;
  }
  
  function setDescription($description){
    $this->description = $description;
  }

  public const HASH_TYPE_ID = "hashTypeId";
  public const DESCRIPTION = "description";
}
