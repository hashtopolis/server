<?php

namespace DBA;

class CoalesceLikeFilterInsensitive extends Filter {
  private $key;
  private $value;
  /**
   * @var AbstractModelFactory
   */
  private $overrideFactory;

  private $columns;
  
  function __construct($columns, $value, $overrideFactory = null) {
    $this->columns = $columns;
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
    $mapped_columns = [];
    foreach($this->columns as $column) {
      array_push($mapped_columns, $table . AbstractModelFactory::getMappedModelKey($factory->getNullObject(), $column));
    }
    
    return "LOWER(" . "COALESCE(" . implode(", ", $mapped_columns) . ") " . ") LIKE LOWER(?)";
  }
  
  function getValue() {
    return $this->value;
  }
  
  function getHasValue(): bool {
    return true;
  }
}
