<?php

/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 02.01.17
 * Time: 23:57
 */

namespace DBA;

class ComparisonFilter extends Filter {
  private $key1;
  private $key2;
  private $operator;
  
  function __construct($key1, $key2, $operator) {
    $this->key1 = $key1;
    $this->key2 = $key2;
    $this->operator = $operator;
  }
  
  function getQueryString($table = "") {
    if ($table != "") {
      $table = $table . ".";
    }
    return $table . $this->key1 . $this->operator . $table . $this->key2;
  }
  
  function getValue() {
    return null;
  }
}

