<?php

/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 02.01.17
 * Time: 23:57
 */

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
    for($x = 0;$x<sizeof($this->values);$x++){
      $app[] = "?";
    }
    return $table . $this->key . " IN (".implode(",", $app).")";
  }
  
  function getValue() {
    return $this->values;
  }
  
  function getHasValue() {
    return true;
  }
}
