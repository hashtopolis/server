<?php

namespace DBA;

class UpdateSet {
  private $key;
  private $value;
  
  function __construct($key, $value) {
    $this->key = $key;
    $this->value = $value;
  }
  
  function getQuery(AbstractModelFactory $factory, bool $includeTable = false): string {
    $table = "";
    if ($includeTable) {
      $table = $factory->getMappedModelTable() . ".";
    }
    
    return $table . AbstractModelFactory::getMappedModelKey($factory->getNullObject(), $this->key) . "=?";
  }
  
  function getValue() {
    return $this->value;
  }
}
