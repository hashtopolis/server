<?php

namespace DBA;

class LikeFilterInsensitive extends Filter {
  private $key;
  private $value;
  /**
   * @var AbstractModelFactory
   */
  private $factory;
  
  function __construct($key, $value, $factory = null) {
    $this->key = $key;
    $this->value = $value;
    $this->factory = $factory;
  }
  
  function getQueryString($table = "") {
    if ($table != "") {
      $table = $table . ".";
    }
    if ($this->factory != null) {
      $table = $this->factory->getModelTable() . ".";
    }
    
    return "LOWER(" . $table . $this->key . ") LIKE LOWER(?)";
  }
  
  function getValue() {
    return $this->value;
  }
  
  function getHasValue() {
    return true;
  }
}
