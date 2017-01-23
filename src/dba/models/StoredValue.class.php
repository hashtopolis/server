<?php

/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 02.01.17
 * Time: 23:57
 */

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
  
  function getVal(){
    return $this->val;
  }
  
  function setVal($val){
    $this->val = $val;
  }

  const STORED_VALUE_ID = "storedValueId";
  const VAL = "val";
}
