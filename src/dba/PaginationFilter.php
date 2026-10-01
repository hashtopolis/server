<?php

namespace Hashtopolis\dba;

class PaginationFilter extends Filter {
  private string $key;
  private mixed $value;
  private string $operator;
  private string $tieBreakerOperator;
  private string $tieBreakerKey;
  private mixed $tieBreakerValue;
  /** @var Filter[] $filters */
  private array $filters;
  
  private ?AbstractModelFactory $overrideFactory;
  
  function __construct($key, $value, $operator, $tieBreakerKey, $tieBreakerValue, $filters = [], $overrideFactory = null, $tieBreakerOperator = null) {
    /**
     * @param QueryFilter[] $filters
     */
    $this->key = $key;
    $this->value = $value;
    $this->operator = $operator;
    $this->tieBreakerOperator = $tieBreakerOperator ?? $operator;
    $this->overrideFactory = $overrideFactory;
    $this->tieBreakerKey = $tieBreakerKey;
    $this->tieBreakerValue = $tieBreakerValue;
    $this->filters = $filters;
  }
  
  function getQueryString(AbstractModelFactory $factory, bool $includeTable = false): string {
    if ($this->overrideFactory != null) {
      $factory = $this->overrideFactory;
    }
    $table = "";
    if ($includeTable) {
      $table = $factory->getMappedModelTable() . ".";
    }
    
    $parts = array_map(fn($filter) => $filter->getQueryString($factory, true), $this->filters);
    //ex. SELECT hashTypeId, description, isSalted, isSlowHash FROM HashType 
    //    where (HashType.isSalted < 1) OR (HashType.isSalted = 1 and HashType.hashTypeId < 12600) 
    //    ORDER BY HashType.isSalted DESC, HashType.hashTypeId DESC LIMIT 25;
    // keys with a sort expression are compared on the expression (also applied to the cursor value), to match the ordering
    $model = $factory->getNullObject();
    $column = AbstractModelFactory::getSortExpression($model, $this->key, $table . AbstractModelFactory::getMappedModelKey($model, $this->key));
    $placeholder = AbstractModelFactory::getSortExpression($model, $this->key, "?");
    $tieBreakerColumn = AbstractModelFactory::getSortExpression($model, $this->tieBreakerKey, $table . AbstractModelFactory::getMappedModelKey($model, $this->tieBreakerKey));
    $tieBreakerPlaceholder = AbstractModelFactory::getSortExpression($model, $this->tieBreakerKey, "?");
    $queryString = "(" . $column . $this->operator . $placeholder . ") OR (" . $column . "=" . $placeholder
      . " AND " . $tieBreakerColumn . $this->tieBreakerOperator . $tieBreakerPlaceholder;
    if (count($this->filters) > 0) {
      $queryString = $queryString . " AND " . implode(" AND ", $parts);
    }
    $queryString .= ")";
    return $queryString;
  }
  
  function getValue(): array {
    $values = [$this->value, $this->value, $this->tieBreakerValue];
    return array_merge($values, array_map(fn($filter) => $filter->getValue(), $this->filters));
  }
  
  function getHasValue(): bool {
    if ($this->value === null) {
      return false;
    }
    return true;
  }
}
