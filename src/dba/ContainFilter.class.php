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
    return $table . $this->key . " IN ?";
  }
  
  function getValue() {
    return implode(",", $this->values);
  }
}
