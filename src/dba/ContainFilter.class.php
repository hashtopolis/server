<?php

namespace DBA;

class ContainFilter extends Filter {
  private $key;
  private $values;
  /**
   * @var AbstractModelFactory
   */
  private $overrideFactory;
  private $inverse;
  
  function __construct($key, $values, $overrideFactory = null, $inverse = false) {
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
  
  function getValue() {
    return $this->values;
  }
  
  function getHasValue() {
    return true;
  }
}
