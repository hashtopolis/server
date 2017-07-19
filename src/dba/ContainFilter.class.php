<?php

namespace DBA;

class ContainFilter extends Filter {
  private $key;
  private $values;
  
  function __construct($key, $values) {
    $this->key = $key;
    $this->values = $values;
  }
  
  function getQueryString($table = "") {
    if ($table != "") {
      $table = $table . ".";
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
