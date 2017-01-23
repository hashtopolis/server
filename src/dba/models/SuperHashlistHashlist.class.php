<?php

/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 02.01.17
 * Time: 23:57
 */

namespace DBA;

class SuperHashlistHashlist extends AbstractModel {
  private $superHashlistHashlistId;
  private $superHashlistId;
  private $hashlistId;
  
  function __construct($superHashlistHashlistId, $superHashlistId, $hashlistId) {
    $this->superHashlistHashlistId = $superHashlistHashlistId;
    $this->superHashlistId = $superHashlistId;
    $this->hashlistId = $hashlistId;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['superHashlistHashlistId'] = $this->superHashlistHashlistId;
    $dict['superHashlistId'] = $this->superHashlistId;
    $dict['hashlistId'] = $this->hashlistId;
    
    return $dict;
  }
  
  function getPrimaryKey() {
    return "superHashlistHashlistId";
  }
  
  function getPrimaryKeyValue() {
    return $this->superHashlistHashlistId;
  }
  
  function getId() {
    return $this->superHashlistHashlistId;
  }
  
  function setId($id) {
    $this->superHashlistHashlistId = $id;
  }
  
  function getSuperHashlistId(){
    return $this->superHashlistId;
  }
  
  function setSuperHashlistId($superHashlistId){
    $this->superHashlistId = $superHashlistId;
  }
  
  function getHashlistId(){
    return $this->hashlistId;
  }
  
  function setHashlistId($hashlistId){
    $this->hashlistId = $hashlistId;
  }

  public const SUPER_HASHLIST_HASHLIST_ID = "superHashlistHashlistId";
  public const SUPER_HASHLIST_ID = "superHashlistId";
  public const HASHLIST_ID = "hashlistId";
}
