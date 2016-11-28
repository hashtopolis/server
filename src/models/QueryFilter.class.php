<?php

class QueryFilter {
  private $key;
  private $value;
  private $operator;
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
    if($this->factory != null){
      $table = $this->factory->getModelTable(). ".";
    }
    
    return $table . $this->key . $this->operator . "?";
  }
  
  function getValue() {
    return $this->value;
  }
}

