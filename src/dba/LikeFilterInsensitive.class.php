<?php

namespace DBA;

class LikeFilterInsensitive extends Filter {
  private $key;
  private $value;
  /**
   * @var AbstractModelFactory
   */
  private $overrideFactory;
  
  function __construct($key, $value, $overrideFactory = null) {
    $this->key = $key;
    $this->value = $value;
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
    
    return "LOWER(" . $table . AbstractModelFactory::getMappedModelKey($factory->getNullObject(),$this->key) . ") LIKE LOWER(?)";
  }
  
  function getValue() {
    return $this->value;
  }
  
  function getHasValue(): bool {
    return true;
  }

  function getKey() {
    return $this->key;
  }
}
