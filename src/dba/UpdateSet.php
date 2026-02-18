<?php

namespace Hashtopolis\dba;

class UpdateSet {
  private string $key;
  private mixed $value;
  
  function __construct(string $key, mixed $value) {
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
  
  function getValue(): mixed {
    return $this->value;
  }
}
