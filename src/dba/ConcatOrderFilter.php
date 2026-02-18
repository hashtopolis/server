<?php

namespace Hashtopolis\dba;

class ConcatOrderFilter extends Order {
  /**
   * @var ConcatColumn[] $columns
   */
  private $columns;
  private $type;
  
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
    foreach($this->columns as $column) {
      $mapped_columns[] = AbstractModelFactory::getMappedModelKey($column->getFactory()->getNullObject(), $column->getValue());
    }
    return "CONCAT(" . implode(", ", $mapped_columns) . ") " . $this->type;
  }
}