<?php

namespace DBA;

class GroupFilter extends Group {
  private $by;
  /**
   * @var AbstractModelFactory
   */
  private $factory;
  
  function __construct($by, $factory = null) {
    $this->by = $by;
    $this->factory = $factory;
  }
  
  function getQueryString($table = "") {
    if ($table != "") {
      $table = $table . ".";
    }
    if ($this->factory != null) {
      $table = $this->factory->getModelTable() . ".";
    }
    
    return $table . $this->by . " ";
  }
}


