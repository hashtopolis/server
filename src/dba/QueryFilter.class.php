<?php

namespace DBA;

class QueryFilter extends Filter {
  private $key;
  private $value;
  private $operator;
  /**
   * @var AbstractModelFactory
   */
  private $overrideFactory;
  
  function __construct($key, $value, $operator, $overrideFactory = null) {
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
  
  function getValue() {
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
