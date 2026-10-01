<?php

namespace Hashtopolis\dba;

class OrderFilter extends Order {
  private string $by;
  private string $type;
  
  private ?AbstractModelFactory $overrideFactory;
  
  function __construct(string $by, string $type, ?AbstractModelFactory $overrideFactory = null) {
    $this->by = $by;
    $this->type = $type;
    $this->overrideFactory = $overrideFactory;
  }
  
  function getBy(): string {
    return $this->by;
  }
  
  function getType(): string {
    return $this->type;
  }
  
  function getQueryString(AbstractModelFactory $factory, bool $includeTable = false): string {
    if ($this->overrideFactory != null) {
      $factory = $this->overrideFactory;
    }
    $table = "";
    if ($includeTable) {
      $table = $factory->getMappedModelTable() . ".";
    }
    
    $column = $table . AbstractModelFactory::getMappedModelKey($factory->getNullObject(), $this->by);
    return AbstractModelFactory::getSortExpression($factory->getNullObject(), $this->by, $column) . " " . $this->type;
  }
}


