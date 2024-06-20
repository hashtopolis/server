<?php

namespace DBA;

use DBA\AbstractModelFactory;

class Aggregation {
  private $column;
  private $function;
  /**
   * @var AbstractModelFactory
   */
  private $factory;
  
  function __construct($column, $function, $factory = null) {
    $this->column = $column;
    $this->function = $function;
    $this->factory = $factory;
  }
  
  function getName() {
    return strtolower($this->function) . "_" . $this->column;
  }
  
  function getQueryString($table = "") {
    if ($table != "") {
      $table = $table . ".";
    }
    if ($this->factory != null) {
      $table = $this->factory->getModelTable() . ".";
    }
    
    return $this->function . "(" . $table . $this->column . ") AS " . $this->getName();
  }
}


