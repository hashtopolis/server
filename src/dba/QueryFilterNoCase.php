<?php

namespace Hashtopolis\dba;

class QueryFilterNoCase extends Filter {
  private string      $key;
  private string|null $value;
  private string      $operator;
  
  private ?AbstractModelFactory $overrideFactory;
  
  function __construct(string $key, string|null $value, string $operator, ?AbstractModelFactory $overrideFactory = null) {
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
    return "(LOWER(" . $table . AbstractModelFactory::getMappedModelKey($factory->getNullObject(), $this->key) . ") " . $this->operator . "? OR " . $table . AbstractModelFactory::getMappedModelKey($factory->getNullObject(), $this->key) . $this->operator . "?)";
  }
  
  function getValue(): ?array {
    if ($this->value === null) {
      return null;
    }
    return array($this->value, $this->value);
  }
  
  function getHasValue(): bool {
    if ($this->value === null) {
      return false;
    }
    return true;
  }
}
