<?php

namespace Hashtopolis\dba;

/**
 * Pagination cursor filter for computed/multi-column sort expressions using CONCAT_WS.
 *
 * Generates SQL of the form:
 *   (CONCAT_WS('', col1, col2, ...) <op> ?)
 *   OR (
 *     CONCAT_WS('', col1, col2, ...) = ?
 *     AND <tieBreakerTable>.<tieBreakerKey> <op> ?
 *     [AND <extra row-level filters>]
 *   )
 *
 * Mirrors the expression used by ConcatOrderFilter and ConcatLikeFilterInsensitive
 * so that ORDER BY, LIKE filtering and cursor pagination all operate on the same value.
 */
class PaginationConcatFilter extends Filter {
  /**
   * @var ConcatColumn[] Columns to combine with CONCAT_WS
   */
  private array $columns;
  private mixed $value;
  private string $operator;
  private string $tieBreakerKey;
  private mixed $tieBreakerValue;
  /**
   * @var QueryFilter[] Extra row-level filters included inside the OR clause
   */
  private array $filters;
  private ?AbstractModelFactory $tieBreakerFactory;

  /**
   * @param ConcatColumn[] $columns          Columns to CONCAT_WS over
   * @param mixed          $value            Primary cursor value (the concatenated string)
   * @param string         $operator         ">" or "<"
   * @param string         $tieBreakerKey    Tiebreaker column key on the tiebreaker factory
   * @param mixed          $tieBreakerValue  Tiebreaker cursor value
   * @param QueryFilter[]  $filters          Extra row-level filters copied from the base query
   * @param AbstractModelFactory|null $tieBreakerFactory Factory for the tiebreaker column (defaults to $factory arg)
   */
  function __construct(
    array $columns,
    mixed $value,
    string $operator,
    string $tieBreakerKey,
    mixed $tieBreakerValue,
    array $filters = [],
    ?AbstractModelFactory $tieBreakerFactory = null
  ) {
    $this->columns = $columns;
    $this->value = $value;
    $this->operator = $operator;
    $this->tieBreakerKey = $tieBreakerKey;
    $this->tieBreakerValue = $tieBreakerValue;
    $this->filters = $filters;
    $this->tieBreakerFactory = $tieBreakerFactory;
  }

  /**
   * Build CONCAT_WS('', col1, col2, ...) with fully-qualified table.column names.
   */
  private function buildConcatExpr(): string {
    $qualifiedCols = [];
    foreach ($this->columns as $column) {
      $colFactory = $column->getFactory();
      $qualifiedCols[] = $colFactory->getMappedModelTable() . '.'
        . AbstractModelFactory::getMappedModelKey($colFactory->getNullObject(), $column->getValue());
    }
    return "CONCAT_WS('', " . implode(', ', $qualifiedCols) . ')';
  }

  function getQueryString(AbstractModelFactory $factory, bool $includeTable = false): string {
    $expr = $this->buildConcatExpr();

    $factory = $this->tieBreakerFactory ?? $factory;
    $tbTable = $factory->getMappedModelTable() . '.';
    $tbKey = AbstractModelFactory::getMappedModelKey($factory->getNullObject(), $this->tieBreakerKey);

    $parts = array_map(fn($f) => $f->getQueryString($factory, true), $this->filters);

    $queryString = "(" . $expr . $this->operator . "?" . ") OR (" . $expr . "=" . "?"
      . " AND " . $tbTable . $tbKey . $this->operator . "?";
    if (count($this->filters) > 0) {
      $queryString = $queryString . " AND " . implode(" AND ", $parts);
    }
    $queryString .= ')';
    return $queryString;
  }

  function getValue(): array {
    $values = [$this->value, $this->value, $this->tieBreakerValue];
    return array_merge($values, array_map(fn($f) => $f->getValue(), $this->filters));
  }

  function getHasValue(): bool {
    return $this->value !== null;
  }
}
