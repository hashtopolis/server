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
  private $isSalted;
  
  function __construct($hashTypeId, $description, $isSalted) {
    $this->hashTypeId = $hashTypeId;
    $this->description = $description;
    $this->isSalted = $isSalted;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['hashTypeId'] = $this->hashTypeId;
    $dict['description'] = $this->description;
    $dict['isSalted'] = $this->isSalted;
    
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
  
  function getIsSalted(){
    return $this->isSalted;
  }
  
  function setIsSalted($isSalted){
    $this->isSalted = $isSalted;
  }

  const HASH_TYPE_ID = "hashTypeId";
  const DESCRIPTION = "description";
  const IS_SALTED = "isSalted";
}
