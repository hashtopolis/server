<?php

namespace DBA;

class ComparisonFilter extends Filter {
  private $key1;
  private $key2;
  private $operator;
  
  /**
   * @var AbstractModelFactory
   */
  private $overrideFactory;
  
  function __construct($key1, $key2, $operator, $overrideFactory = null) {
    $this->key1 = $key1;
    $this->key2 = $key2;
    $this->operator = $operator;
    $this->overrideFactory = $overrideFactory;
  }
  
  function getQueryString($table = "") {
    if ($table != "") {
      $table = $table . ".";
    }
    if ($this->overrideFactory != null) {
      $table = $this->overrideFactory->getModelTable() . ".";
    }
    
    return $table . $this->key1 . $this->operator . $table . $this->key2;
  }
  
  function getValue() {
    return null;
  }
  
  function getHasValue() {
    return false;
  }
}

