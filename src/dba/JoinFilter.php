<?php

namespace Hashtopolis\dba;

class JoinFilter extends Join {
  private ?AbstractModelFactory $otherFactory;
  
  private string $match1;
  private string $match2;
  
  private string $otherTableName;
  
  private ?AbstractModelFactory $overrideOwnFactory;

  private string $joinType;

  /**
   * @var QueryFilter[] array of QueryFilters that have to be performed on the join
   */
  private array $queryFilters;

  // string constants for the join types
  public const INNER = "INNER";
  public const LEFT = "LEFT";
  public const RIGHT = "RIGHT";
  
  /**
   * @param AbstractModelFactory $otherFactory
   * @param string $matching1
   * @param string $matching2
   * @param ?AbstractModelFactory $overrideOwnFactory
   * @param string $joinType
   * @param array $queryFilters
   */
  function __construct(AbstractModelFactory $otherFactory, string $matching1, string $matching2, ?AbstractModelFactory $overrideOwnFactory = null, string $joinType = JoinFilter::INNER, array $queryFilters = []) {
    $this->otherFactory = $otherFactory;
    $this->match1 = $matching1;
    $this->match2 = $matching2;
    $this->joinType = $joinType;
    $this->queryFilters = $queryFilters;
    
    $this->otherTableName = $this->otherFactory->getMappedModelTable();
    $this->overrideOwnFactory = $overrideOwnFactory;
  }
  
  /**
   * @return AbstractModelFactory
   */
  function getOtherFactory(): AbstractModelFactory {
    return $this->otherFactory;
  }
  
  function getMatch1(): string {
    return $this->match1;
  }
  
  function getMatch2(): string {
    return $this->match2;
  }
  
  function getOtherTableName(): string {
    return $this->otherTableName;
  }

  function getJoinType(): string {
    return $this->joinType;
  }

  function setJoinType($joinType): void {
    $this->joinType = $joinType;
  }

  function getQueryFilters(): array {
    return $this->queryFilters;
  }
  
  function setQueryFilters(array $queryFilters): void {
    $this->queryFilters = $queryFilters;
  }
  
  function getOverrideOwnFactory(): ?AbstractModelFactory {
    return $this->overrideOwnFactory;
  }
}


