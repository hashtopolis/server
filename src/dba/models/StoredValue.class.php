<?php

namespace DBA;

class StoredValue extends AbstractModel {
  private $storedValueId;
  private $val;
  
  function __construct($storedValueId, $val) {
    $this->storedValueId = $storedValueId;
    $this->val = $val;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['storedValueId'] = $this->storedValueId;
    $dict['val'] = $this->val;
    
    return $dict;
  }
  
  function getPrimaryKey() {
    return "storedValueId";
  }
  
  function getPrimaryKeyValue() {
    return $this->storedValueId;
  }
  
  function getId() {
    return $this->storedValueId;
  }
  
  function setId($id) {
    $this->storedValueId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose() {
    return get_object_vars($this);
  }
  
  function getVal() {
    return $this->val;
  }
  
  function setVal($val) {
    $this->val = $val;
  }
  
  const STORED_VALUE_ID = "storedValueId";
  const VAL = "val";
}
