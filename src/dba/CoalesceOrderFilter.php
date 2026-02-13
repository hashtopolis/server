<?php

namespace Hashtopolis\dba;

class CoalesceOrderFilter extends Order {
  // The columns to do the COALESCE function on
  private array  $columns;
  private string $type;
  
  /**
   * @param string[] $columns
   * @param string $type
   */
  function __construct(array $columns, string $type) {
    $this->columns = $columns;
    $this->type = $type;
  }
  
  function getQueryString(AbstractModelFactory $factory, bool $includeTable = false): string {
    $mapped_columns = [];
    foreach ($this->columns as $column) {
      $mapped_columns[] = AbstractModelFactory::getMappedModelKey($factory->getNullObject(), $column);
    }
    return "COALESCE(" . implode(", ", $mapped_columns) . ") " . $this->type;
  }
}