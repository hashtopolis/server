<?php

namespace DBA;

class HashlistHashlist extends AbstractModel {
  private $hashlistHashlistId;
  private $parentHashlistId;
  private $hashlistId;
  
  function __construct($hashlistHashlistId, $parentHashlistId, $hashlistId) {
    $this->hashlistHashlistId = $hashlistHashlistId;
    $this->parentHashlistId = $parentHashlistId;
    $this->hashlistId = $hashlistId;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['hashlistHashlistId'] = $this->hashlistHashlistId;
    $dict['parentHashlistId'] = $this->parentHashlistId;
    $dict['hashlistId'] = $this->hashlistId;
    
    return $dict;
  }
  
  static function getFeatures() {
    $dict = array();
    $dict['hashlistHashlistId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "hashlistHashlistId"];
    $dict['parentHashlistId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "parentHashlistId"];
    $dict['hashlistId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "hashlistId"];

    return $dict;
  }

  function getPrimaryKey() {
    return "hashlistHashlistId";
  }
  
  function getPrimaryKeyValue() {
    return $this->hashlistHashlistId;
  }
  
  function getId() {
    return $this->hashlistHashlistId;
  }
  
  function setId($id) {
    $this->hashlistHashlistId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose() {
    return get_object_vars($this);
  }
  
  function getParentHashlistId() {
    return $this->parentHashlistId;
  }
  
  function setParentHashlistId($parentHashlistId) {
    $this->parentHashlistId = $parentHashlistId;
  }
  
  function getHashlistId() {
    return $this->hashlistId;
  }
  
  function setHashlistId($hashlistId) {
    $this->hashlistId = $hashlistId;
  }
  
  const HASHLIST_HASHLIST_ID = "hashlistHashlistId";
  const PARENT_HASHLIST_ID = "parentHashlistId";
  const HASHLIST_ID = "hashlistId";

  const PERM_CREATE = "permHashlistHashlistCreate";
  const PERM_READ = "permHashlistHashlistRead";
  const PERM_UPDATE = "permHashlistHashlistUpdate";
  const PERM_DELETE = "permHashlistHashlistDelete";
}
