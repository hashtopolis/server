<?php

namespace DBA;

use DBA\AbstractModelFactory;

class Aggregation {
  private $column;
  private $function;
  /**
   * @var AbstractModelFactory
   */
  private $overrideFactory;
  
  const SUM   = "SUM";
  const MAX   = "MAX";
  const MIN   = "MIN";
  const COUNT = "COUNT";
  
  function __construct($column, $function, $overrideFactory = null) {
    $this->column = $column;
    $this->function = $function;
    $this->overrideFactory = $overrideFactory;
  }
  
  function getName() {
    return strtolower($this->function) . "_" . $this->column;
  }
  
  function getQueryString(AbstractModelFactory $factory, bool $includeTable = false) {
    if ($this->overrideFactory != null) {
      $factory = $this->overrideFactory;
    }
    $table = "";
    if ($includeTable) {
      $table = $factory->getMappedModelTable() . ".";
    }
    
    return $this->function . "(" . $table . AbstractModelFactory::getMappedModelKey($factory->getNullObject(), $this->column) . ") AS " . $this->getName();
  }
}


