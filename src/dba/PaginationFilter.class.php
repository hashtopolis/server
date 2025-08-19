<?php

namespace DBA;

class PaginationFilter extends Filter {
  private $key;
  private $value;
  private $operator;
  private $tieBreakerKey;
  private $tieBreakerValue;
  /**
   * @var AbstractModelFactory
   */
  private $factory;
  
  function __construct($key, $value, $operator, $tieBreakerKey, $tieBreakerValue, $factory = null) {
    $this->key = $key;
    $this->value = $value;
    $this->operator = $operator;
    $this->factory = $factory;
    $this->tieBreakerKey = $tieBreakerKey;
    $this->tieBreakerValue = $tieBreakerValue;
  }
  
  function getQueryString($table = "") {
    if ($table != "") {
      $table = $table . ".";
    }
    if ($this->factory != null) {
      $table = $this->factory->getModelTable() . ".";
    }
    //ex. SELECT hashTypeId, description, isSalted, isSlowHash FROM HashType 
    //    where (HashType.isSalted < 1) OR (HashType.isSalted = 1 and HashType.hashTypeId < 12600) 
    //    ORDER BY HashType.isSalted DESC, HashType.hashTypeId DESC LIMIT 25;
    return "(" . $table . $this->key . $this->operator . "?" . ") OR (" . $this->key . "=" . "?" 
    . " AND " . $this->tieBreakerKey . $this->operator . "?)";
  }
  
  function getValue() {
    return [$this->value, $this->value, $this->tieBreakerValue];
  }
  
  function getHasValue() {
    if ($this->value === null) {
      return false;
    }
    return true;
  }
}
