<?php

namespace DBA;

class CoalesceOrderFilter extends Order {
  // The columns to do the COALESCE function on
  /**
   * @var CoalesceColumn[] $columns
   */
  private $columns;
  private $type;
  
  function __construct($columns, $type) {
    $this->columns = $columns;
    $this->type = $type;
  }
  
  function getQueryString(AbstractModelFactory $factory, bool $includeTable = false): string {
    $mapped_columns = [];
    foreach($this->columns as $column) {
      array_push($mapped_columns, AbstractModelFactory::getMappedModelKey($column->getFactory()->getNullObject(), $column->getValue()));
    }
    return "COALESCE(" . implode(", ", $mapped_columns) . ") " . $this->type;
  }
}