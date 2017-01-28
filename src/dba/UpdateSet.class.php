<?php
class UpdateSet {
  private $key;
  private $value;
  
  function __construct($key, $value) {
    $this->key = $key;
    $this->value = $value;
  }
  
  function getQuery($table = "") {
    return $table . $this->key . "=?";
  }
  
  function getValue() {
    if($this->value === null){
      return "NULL";
    }
    return $this->value;
  }
}
