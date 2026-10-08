<?php

namespace Hashtopolis\dba;

class PaginationFilter extends Filter {
  private string $key;
  private mixed $value;
  private string $operator;
  private string $tieBreakerOperator;
  private string $tieBreakerKey;
  private mixed $tieBreakerValue;

  private ?AbstractModelFactory $overrideFactory;

  function __construct($key, $value, $operator, $tieBreakerKey, $tieBreakerValue, $overrideFactory = null, $tieBreakerOperator = null) {
    $this->key = $key;
    $this->value = $value;
    $this->operator = $operator;
    $this->tieBreakerOperator = $tieBreakerOperator ?? $operator;
    $this->overrideFactory = $overrideFactory;
    $this->tieBreakerKey = $tieBreakerKey;
    $this->tieBreakerValue = $tieBreakerValue;
  }
  
  function getQueryString(AbstractModelFactory $factory, bool $includeTable = false): string {
    if ($this->overrideFactory != null) {
      $factory = $this->overrideFactory;
    }
    $table = "";
    if ($includeTable) {
      $table = $factory->getMappedModelTable() . ".";
    }
    
    //ex. SELECT hashTypeId, description, isSalted, isSlowHash FROM HashType
    //    where ((HashType.isSalted < 1) OR (HashType.isSalted = 1 and HashType.hashTypeId < 12600))
    //    ORDER BY HashType.isSalted DESC, HashType.hashTypeId DESC LIMIT 25;
    // the whole condition is wrapped in parentheses, as it is AND-combined with the other filters (e.g. ACL filters)
    // keys with a sort expression are compared on the expression (also applied to the cursor value), to match the ordering
    $model = $factory->getNullObject();
    $column = AbstractModelFactory::getSortExpression($model, $this->key, $table . AbstractModelFactory::getMappedModelKey($model, $this->key));
    $placeholder = AbstractModelFactory::getSortExpression($model, $this->key, "?");
    $tieBreakerColumn = AbstractModelFactory::getSortExpression($model, $this->tieBreakerKey, $table . AbstractModelFactory::getMappedModelKey($model, $this->tieBreakerKey));
    $tieBreakerPlaceholder = AbstractModelFactory::getSortExpression($model, $this->tieBreakerKey, "?");
    return "((" . $column . $this->operator . $placeholder . ") OR (" . $column . "=" . $placeholder
      . " AND " . $tieBreakerColumn . $this->tieBreakerOperator . $tieBreakerPlaceholder . "))";
  }

  function getValue(): array {
    return [$this->value, $this->value, $this->tieBreakerValue];
  }
  
  function getHasValue(): bool {
    if ($this->value === null) {
      return false;
    }
    return true;
  }
}
