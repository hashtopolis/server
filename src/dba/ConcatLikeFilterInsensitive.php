<?php

namespace Hashtopolis\dba;

class ConcatLikeFilterInsensitive extends Filter {
  private string $value;

  /**
   * @var ConcatColumn[] $columns
   */
  private array $columns;
  
  function __construct($columns, $value) {
    $this->columns = $columns;
    $this->value = $value;
  }
  
  function getQueryString(AbstractModelFactory $factory, bool $includeTable = false): string {
    $mapped_columns = [];
    foreach($this->columns as $column) {
      $columnFactory = $column->getFactory();
      $mapped_columns[] = $columnFactory->getMappedModelTable() . "." . AbstractModelFactory::getMappedModelKey($columnFactory->getNullObject(), $column->getValue());
    }
    return "LOWER(" . "CONCAT(" . implode(", ", $mapped_columns) . ")" . ") LIKE LOWER(?)";
  }
  
  function getValue(): string {
    return $this->value;
  }
  
  function getHasValue(): bool {
    return true;
  }
}
