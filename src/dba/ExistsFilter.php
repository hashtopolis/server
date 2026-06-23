<?php

namespace Hashtopolis\dba;

class ExistsFilter extends Filter {
  private AbstractModelFactory $subqueryFactory;
  private string $subqueryMatchKey;
  private string $outerMatchKey;
  private array $filters;
  private ?QueryFilter $baseFilter;
  private bool $inverse;

  /**
  * Build a correlated EXISTS filter with additional subquery filters.
   *
   * Example output:
   * EXISTS (
   *   SELECT 1
   *   FROM AccessGroupAgent aga
   *   WHERE aga.agentId = Chunk.agentId
   *     AND aga.accessGroupId IN (?, ?)
   * )
   */
  function __construct(
    AbstractModelFactory $subqueryFactory,
    string $subqueryMatchKey,
    string $outerMatchKey,
    array $filters = [],
    ?QueryFilter $baseFilter = null,
    bool $inverse = false
  ) {
    /** @var Filter[] $filters */
    $this->subqueryFactory = $subqueryFactory;
    $this->subqueryMatchKey = $subqueryMatchKey;
    $this->outerMatchKey = $outerMatchKey;
    $this->filters = $filters;
    $this->baseFilter = $baseFilter;
    $this->inverse = $inverse;
  }

  function getQueryString(AbstractModelFactory $factory, bool $includeTable = false): string {
    $outerTable = $factory->getMappedModelTable();
    $subqueryTable = $this->subqueryFactory->getMappedModelTable();

    $subqueryMatchColumn = AbstractModelFactory::getMappedModelKey($this->subqueryFactory->getNullObject(), $this->subqueryMatchKey);
    $outerMatchColumn = AbstractModelFactory::getMappedModelKey($factory->getNullObject(), $this->outerMatchKey);
    $existsPrefix = $this->inverse ? "NOT EXISTS" : "EXISTS";
    $parts = array_map(fn($filter) => $filter->getQueryString($this->subqueryFactory, true), $this->filters);

    $query = $existsPrefix . " (SELECT 1 FROM " . $subqueryTable
      . " WHERE " . $subqueryTable . "." . $subqueryMatchColumn . "=" . $outerTable . "." . $outerMatchColumn;

    if (count($parts) > 0) {
      $query .= " AND " . implode(" AND ", $parts);
    }
    $query .= ")";

    if ($this->baseFilter !== null) {
      $query = "(" . $query . " OR " . $this->baseFilter->getQueryString($factory, true) . ")";
    }

    return $query;
  }

  function getValue(): array {
    $values = [];
    foreach ($this->filters as $filter) {
      if (!$filter->getHasValue()) {
        continue;
      }

      $value = $filter->getValue();
      if (is_array($value)) {
        foreach ($value as $v) {
          $values[] = $v;
        }
      }
      else {
        $values[] = $value;
      }
    }
    return $values;
  }

  function getHasValue(): bool {
    foreach ($this->filters as $filter) {
      if ($filter->getHasValue()) {
        return true;
      }
    }
    return false;
  }
}
