<?php

namespace DBA;

class GroupFilter extends Group {
  private $by;
  /**
   * @var AbstractModelFactory
   */
  private $overrideFactory;
  
  function __construct($by, $overrideFactory = null) {
    $this->by = $by;
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
    
    return $table . AbstractModelFactory::getMappedModelKey($factory->getNullObject(), $this->by) . " ";
  }
}


