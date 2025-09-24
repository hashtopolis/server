<?php

namespace DBA;

class PaginationFilter extends Filter {
  private $key;
  private $value;
  private $operator;
  private $tieBreakerKey;
  private $tieBreakerValue;
  private $filters;
  /**
   * @var AbstractModelFactory
   */
  private $factory;
  
  function __construct($key, $value, $operator, $tieBreakerKey, $tieBreakerValue, $filters = [], $factory = null) {
    /**
     * @param QueryFilter[] $filters
     */
    $this->key = $key;
    $this->value = $value;
    $this->operator = $operator;
    $this->factory = $factory;
    $this->tieBreakerKey = $tieBreakerKey;
    $this->tieBreakerValue = $tieBreakerValue;
    $this->filters = $filters;
  }
  
  function getQueryString($table = "") {
    if ($table != "") {
      $table = $table . ".";
    }
    if ($this->factory != null) {
      $table = $this->factory->getModelTable() . ".";
    }
    $parts = array_map(fn($filter) => $filter->getQueryString(), $this->filters);
    //ex. SELECT hashTypeId, description, isSalted, isSlowHash FROM HashType 
    //    where (HashType.isSalted < 1) OR (HashType.isSalted = 1 and HashType.hashTypeId < 12600) 
    //    ORDER BY HashType.isSalted DESC, HashType.hashTypeId DESC LIMIT 25;
    $queryString = "(" . $table . $this->key . $this->operator . "?" . ") OR (" . $this->key . "=" . "?" 
    . " AND " . $this->tieBreakerKey . $this->operator . "?";
    if (count($this->filters) > 0) {
      $queryString = $queryString . " AND ". implode(" AND ", $parts);
    }
    $queryString .= ")"; 
    return $queryString;
  }
  
  function getValue() {
    $values = [$this->value, $this->value, $this->tieBreakerValue];
    return array_merge($values, array_map(fn($filter) => $filter->getValue(), $this->filters));
  }
  
  function getHasValue() {
    if ($this->value === null) {
      return false;
    }
    return true;
  }
}