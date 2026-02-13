<?php

namespace Hashtopolis\dba;

class Aggregation {
  private string $column;
  private string $function;
  
  private ?AbstractModelFactory $overrideFactory;
  
  const SUM   = "SUM";
  const MAX   = "MAX";
  const MIN   = "MIN";
  const COUNT = "COUNT";
  
  function __construct(string $column, string $function, ?AbstractModelFactory $overrideFactory = null) {
    $this->column = $column;
    $this->function = $function;
    $this->overrideFactory = $overrideFactory;
  }
  
  function getName(): string {
    return strtolower($this->function) . "_" . strtolower($this->column);
  }
  
  function getQueryString(AbstractModelFactory $factory, bool $includeTable = false): string {
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


