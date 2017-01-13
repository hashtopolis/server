<?php

/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 02.01.17
 * Time: 23:57
 */

namespace DBA;

class QueryFilter extends Filter {
  private $key;
  private $value;
  private $operator;
  
  function __construct($key, $value, $operator) {
    $this->key = $key;
    $this->value = $value;
    $this->operator = $operator;
  }
  
  function getQueryString($table = "") {
    if ($table != "") {
      $table = $table . ".";
    }
    if ($this->value == 'NULL') {
      return $table . $this->key . " IS NULL";
    }
    
    return $table . $this->key . $this->operator . "?";
  }
  
  function getValue() {
    return $this->value;
  }
}

