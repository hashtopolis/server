<?php

namespace DBA;

class ContainFilter extends Filter {
  private $key;
  private $values;
  /**
   * @var AbstractModelFactory
   */
  private $factory;
  private $inverse;
  
  function __construct($key, $values, $factory = null, $inverse = false) {
    $this->key = $key;
    $this->values = $values;
    $this->factory = $factory;
    $this->inverse = $inverse;
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
      if ($this->inverse) {
        return "TRUE";
      }
      return "FALSE";
    }
    return $table . $this->key . (($this->inverse) ? " NOT" : "") . " IN (" . implode(",", $app) . ")";
  }
  
  function getValue() {
    return $this->values;
  }
  
  function getHasValue() {
    return true;
  }
}
