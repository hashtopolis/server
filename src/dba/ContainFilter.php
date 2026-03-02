<?php

namespace Hashtopolis\dba;

class ContainFilter extends Filter {
  private string $key;
  private array $values;
  
  private ?AbstractModelFactory $overrideFactory;
  private bool $inverse;
  
  function __construct(string $key, array $values, ?AbstractModelFactory $overrideFactory = null, bool $inverse = false) {
    $this->key = $key;
    $this->values = $values;
    $this->overrideFactory = $overrideFactory;
    $this->inverse = $inverse;
  }
  
  function getQueryString(AbstractModelFactory $factory, bool $includeTable = false): string {
    if ($this->overrideFactory != null) {
      $factory = $this->overrideFactory;
    }
    $table = "";
    if ($includeTable) {
      $table = $factory->getMappedModelTable() . ".";
    }
    
    $app = array();
    for ($x = 0; $x < sizeof($this->values); $x++) {
      $app[] = "?";
    }
    if (sizeof($app) == 0) {
      if ($this->inverse) {
        return "TRUE";
      }
      return "FALSE";
    }
    return $table . AbstractModelFactory::getMappedModelKey($factory->getNullObject(), $this->key) . (($this->inverse) ? " NOT" : "") . " IN (" . implode(",", $app) . ")";
  }
  
  function getValue(): array {
    return $this->values;
  }
  
  function getHasValue(): bool {
    return true;
  }
}
