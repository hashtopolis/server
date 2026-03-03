<?php

namespace Hashtopolis\dba;

class LikeFilterInsensitive extends Filter {
  private string $key;
  private string $value;
  
  private ?AbstractModelFactory $overrideFactory;
  
  function __construct(string $key, string $value, ?AbstractModelFactory $overrideFactory = null) {
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
  
  function getValue(): string {
    return $this->value;
  }
  
  function getHasValue(): bool {
    return true;
  }

  function getKey() {
    return $this->key;
  }
}
