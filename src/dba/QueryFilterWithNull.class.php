<?php

namespace DBA;

class QueryFilterWithNull extends Filter {
  private $key;
  private $value;
  private $operator;
  private $matchNull;
  /**
   * @var AbstractModelFactory
   */
  private $factory;
  
  function __construct($key, $value, $operator, $matchNull, $factory = null) {
    $this->key = $key;
    $this->value = $value;
    $this->operator = $operator;
    $this->factory = $factory;
    $this->matchNull = $matchNull;
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
    if ($this->matchNull) {
      return "(" . $table . $this->key . $this->operator . "? OR " . $table . $this->key . " IS NULL)";
    }
    return "(" . $table . $this->key . $this->operator . "? OR " . $table . $this->key . " IS NOT NULL)";
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
