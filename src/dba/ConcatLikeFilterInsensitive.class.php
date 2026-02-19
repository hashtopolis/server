<?php

namespace DBA;

class ConcatLikeFilterInsensitive extends Filter {
  private $value;
  /**
   * @var AbstractModelFactory
   */
  private $overrideFactory;

  /**
   * @var ConcatColumn[] $columns
   */
  private array $columns;
  
  function __construct($columns, $value, $overrideFactory = null) {
    $this->columns = $columns;
    $this->value = $value;
    $this->overrideFactory = $overrideFactory;
  }
  
  function getQueryString(AbstractModelFactory $factory, bool $includeTable = false): string {
    if ($this->overrideFactory != null) {
      $factory = $this->overrideFactory;
    }
    $mapped_columns = [];
    foreach($this->columns as $column) {
      $columnFactory = $column->getFactory();
      array_push($mapped_columns, $columnFactory->getMappedModelTable() . "." . AbstractModelFactory::getMappedModelKey($columnFactory->getNullObject(), $column->getValue()));
    }
    return "LOWER(" . "CONCAT(" . implode(", ", $mapped_columns) . ")" . ") LIKE LOWER(?)";
  }
  
  function getValue() {
    return $this->value;
  }
  
  function getHasValue(): bool {
    return true;
  }
}
