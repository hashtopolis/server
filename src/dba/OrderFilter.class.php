<?php

namespace DBA;

class OrderFilter extends Order {
  private $by;
  private $type;
  /**
   * @var AbstractModelFactory
   */
  private $factory;
  
  function __construct($by, $type, $factory = null) {
    $this->by = $by;
    $this->type = $type;
    $this->factory = $factory;
  }
  
  function getQueryString($table = "") {
    if ($table != "") {
      $table = $table . ".";
    }
    if ($this->factory != null) {
      $table = $this->factory->getModelTable() . ".";
    }
    
    return $table . $this->by . " " . $this->type;
  }
}


