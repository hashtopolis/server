<?php

namespace DBA;

class ContainFilter extends Filter {
  private $key;
  private $values;
  /**
   * @var AbstractModelFactory
   */
  private $factory;
  
  function __construct($key, $values, $factory = null) {
    $this->key = $key;
    $this->values = $values;
    $this->factory = $factory;
  }
  
  function getQueryString($table = "") {
    if ($table != "") {
      $table = $table . ".";
    }
    if ($this->factory != null) {
      $table = $this->factory->getModelTable() . ".";
    }
    
    $app = array();
    for ($x = 0; $x < sizeof($this->values); $x++) {
      $app[] = "?";
    }
    if (sizeof($app) == 0) {
      return "FALSE";
    }
    return $table . $this->key . " IN (" . implode(",", $app) . ")";
  }
  
  function getValue() {
    return $this->values;
  }
  
  function getHasValue() {
    return true;
  }
}
