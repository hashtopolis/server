<?php

namespace Hashtopolis\dba;

use Hashtopolis\inc\StartupConfig;

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
    
    $column = $table . AbstractModelFactory::getMappedModelKey($factory->getNullObject(), $this->key);
    
    // test if we do this on an integer column, if yes, we do not apply LOWER() and need to cast it
    if (str_starts_with($factory->getNullObject()->getFeatures()[$this->key]['type'], 'int')) {
      if (StartupConfig::getInstance()->getDatabaseType() == 'postgres') {
        return $column . "::text LIKE LOWER(?)";
      }
      return "CONVERT(" . $column . ", CHAR) LIKE LOWER(?)";
    }
    
    return "LOWER(" . $column . ") LIKE LOWER(?)";
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
