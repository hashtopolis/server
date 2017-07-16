<?php

namespace DBA;

class QueryFilter extends Filter {
  private $key;
  private $value;
  private $operator;
  /**
   * @var AbstractModelFactory
   */
  private $factory;
  
  function __construct($key, $value, $operator, $factory = null) {
    $this->key = $key;
    $this->value = $value;
    $this->operator = $operator;
    $this->factory = $factory;
  }
  
  function getQueryString($table = "") {
    if ($table != "") {
      $table = $table . ".";
    }
    if ($this->factory != null) {
      $table = $this->factory->getModelTable() . ".";
    }
    
    if ($this->value === null) {
      if ($this->operator == '<>') {
        return $table . $this->key . " IS NOT NULL ";
      }
      return $table . $this->key . " IS NULL ";
    }
    return $table . $this->key . $this->operator . "?";
  }
  
  function getValue() {
    if ($this->value === null) {
      return null;
    }
    return $this->value;
  }
  
  function getHasValue() {
    if ($this->value === null) {
      return false;
    }
    return true;
  }
}
