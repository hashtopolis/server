<?php

namespace DBA;

class LikeFilter extends Filter {
  private $key;
  private $value;
  private $match;
  /**
   * @var AbstractModelFactory
   */
  private $factory;
  
  function __construct($key, $value, $factory = null) {
    $this->key = $key;
    $this->value = $value;
    $this->factory = $factory;
    $this->match = true;
  }
  
  function setMatch($status) {
    $this->match = $status;
  }
  
  function getQueryString($table = "") {
    if ($table != "") {
      $table = $table . ".";
    }
    if ($this->factory != null) {
      $table = $this->factory->getModelTable() . ".";
    }
    
    $inv = "";
    if ($this->match === false) {
      $inv = " NOT";
    }
    
    return $table . $this->key . $inv . " LIKE BINARY ?";
  }
  
  function getValue() {
    return $this->value;
  }
  
  function getHasValue() {
    return true;
  }
}
