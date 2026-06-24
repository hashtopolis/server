<?php

namespace Hashtopolis\dba;

class QueryFilter extends Filter {
  private string $key;
  private mixed $value;
  private string $operator;
  
  private ?AbstractModelFactory $overrideFactory;
  
  function __construct(string $key, mixed $value, string $operator, ?AbstractModelFactory $overrideFactory = null) {
    $this->key = $key;
    $this->value = $value;
    $this->operator = $operator;
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
    
    if ($this->value === null) {
      if ($this->operator == '<>') {
        return $table . AbstractModelFactory::getMappedModelKey($factory->getNullObject(), $this->key) . " IS NOT NULL ";
      }
      return $table . AbstractModelFactory::getMappedModelKey($factory->getNullObject(), $this->key) . " IS NULL ";
    }
    return $table . AbstractModelFactory::getMappedModelKey($factory->getNullObject(), $this->key) . $this->operator . "?";
  }
  
  function getValue(): mixed {
    if ($this->value === null) {
      return null;
    }
    return $this->value;
  }
  
  function getHasValue(): bool {
    if ($this->value === null) {
      return false;
    }
    return true;
  }
}
