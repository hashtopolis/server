<?php

namespace Hashtopolis\dba;

class GroupFilter extends Group {
  private string $by;
  private ?AbstractModelFactory $overrideFactory;
  
  function __construct(string $by, ?AbstractModelFactory $overrideFactory = null) {
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


