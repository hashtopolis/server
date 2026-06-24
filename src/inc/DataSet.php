<?php

namespace Hashtopolis\inc;

class DataSet {
  private $values;
  
  public function __construct($arr = array()) {
    $this->values = $arr;
  }
  
  public function setValues($arr) {
    $this->values = $arr;
  }
  
  public function addValue($key, $val) {
    $this->values[$key] = $val;
  }
  
  public function getVal($key) {
    if (isset($this->values[$key])) {
      return $this->values[$key];
    }
    return false;
  }
  
  public function getKeys() {
    $keys = [];
    foreach ($this->values as $key => $val) {
      $keys[] = $key;
    }
    return $keys;
  }
  
  public function getAllValues() {
    return $this->values;
  }
}