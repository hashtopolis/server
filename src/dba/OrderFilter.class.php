<?php

namespace DBA;

class OrderFilter extends Order {
  private $by;
  private $type;
  /**
   * @var AbstractModelFactory
   */
  private $overrideFactory;
  
  function __construct($by, $type, $overrideFactory = null) {
    $this->by = $by;
    $this->type = $type;
    $this->overrideFactory = $overrideFactory;
  }
  
  function getQueryString(AbstractModelFactory $factory, bool $includeTable = false): string {
    if ($this->overrideFactory != null) {
      $factory = $this->overrideFactory;
    }
    $table = "";
    if ($includeTable) {
      $table = $factory->getMappedModelTable() . ".";
    }
    
    return $table . AbstractModelFactory::getMappedModelKey($factory->getNullObject(), $this->by) . " " . $this->type;
  }
}


