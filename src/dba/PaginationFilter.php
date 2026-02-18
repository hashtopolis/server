<?php

namespace Hashtopolis\dba;

class PaginationFilter extends Filter {
  private $key;
  private $value;
  private $operator;
  private $tieBreakerKey;
  private $tieBreakerValue;
  private $filters;
  /**
   * @var
   */
  private ?AbstractModelFactory $overrideFactory;
  
  function __construct($key, $value, $operator, $tieBreakerKey, $tieBreakerValue, $filters = [], $overrideFactory = null) {
    /**
     * @param QueryFilter[] $filters
     */
    $this->key = $key;
    $this->value = $value;
    $this->operator = $operator;
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
    $queryString = "(" . $table . AbstractModelFactory::getMappedModelKey($factory->getNullObject(), $this->key) . $this->operator . "?" . ") OR (" . AbstractModelFactory::getMappedModelKey($factory->getNullObject(), $this->key) . "=" . "?"
      . " AND " . AbstractModelFactory::getMappedModelKey($factory->getNullObject(), $this->tieBreakerKey) . $this->operator . "?";
    if (count($this->filters) > 0) {
      $queryString = $queryString . " AND " . implode(" AND ", $parts);
    }
    $queryString .= ")";
    return $queryString;
  }
  
  function getValue() {
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
