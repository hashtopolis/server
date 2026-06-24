<?php

namespace Hashtopolis\dba;

use RuntimeException;

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
    $this->function = strtoupper($function);
    $this->overrideFactory = $overrideFactory;
    
    // test for function validity
    if (!in_array($this->function, [Aggregation::SUM, Aggregation::MAX, Aggregation::MIN, Aggregation::COUNT])) {
      throw new RuntimeException("Invalid function for aggregation!");
    }
    
    // in case an overrideFactory used, check that the column is matching
    if ($this->overrideFactory != null && !in_array($this->column, array_keys($this->overrideFactory->getNullObject()->getKeyValueDict()))) {
      throw new RuntimeException("Provided column for aggregation does not match to overrideFactory!");
    }
  }
  
  function getName(): string {
    return strtolower($this->function) . "_" . strtolower($this->column);
  }
  
  function getQueryString(AbstractModelFactory $factory, bool $includeTable = false): string {
    if ($this->overrideFactory != null) {
      $factory = $this->overrideFactory;
    }
    else if (!in_array($this->column, array_keys($factory->getNullObject()->getKeyValueDict()))) {
      throw new RuntimeException("Provided column for aggregation does not match to factory!");
    }
    
    $table = "";
    if ($includeTable) {
      $table = $factory->getMappedModelTable() . ".";
    }
    
    return $this->function . "(" . $table . AbstractModelFactory::getMappedModelKey($factory->getNullObject(), $this->column) . ") AS " . $this->getName();
  }
}


