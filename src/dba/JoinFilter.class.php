<?php

namespace DBA;

class JoinFilter extends Join {
  /**
   * @var AbstractModelFactory
   */
  private $otherFactory;
  
  /**
   * @var string
   */
  private $match1;
  
  /**
   * @var string
   */
  private $match2;
  
  /**
   * @var string
   */
  private $otherTableName;
  
  /**
   * @var AbstractModelFactory
   */
  private $overrideOwnFactory;

  /**
   * @var string
   */
  private $joinType;

  /**
   * @var QueryFilter[] array of queryfilters that have to be performed on the join
   */
  private $queryFilters;
  
  /**
   * JoinFilter constructor.
   * @param $otherFactory AbstractModelFactory
   * @param $matching1 string
   * @param $matching2 string
   * @param $overrideOwnFactory AbstractModelFactory
   * @param $joinType string is normally inner, left or right
   */
  function __construct($otherFactory, $matching1, $matching2, $overrideOwnFactory = null, $joinType = "inner", $queryFilters = []) {
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
  function getOtherFactory() {
    return $this->otherFactory;
  }
  
  function getMatch1() {
    return $this->match1;
  }
  
  function getMatch2() {
    return $this->match2;
  }
  
  function getOtherTableName() {
    return $this->otherTableName;
  }

  function getJoinType() {
    return $this->joinType;
  }

  function setJoinType($joinType) {
    $this->joinType = $joinType;
  }


  function getQueryFilters() {
    return $this->queryFilters;
  }
  
  function setQueryFilters(array $queryFilters) {
    $this->queryFilters = $queryFilters;
  }
  
  /**
   * @return AbstractModelFactory
   */
  function getOverrideOwnFactory() {
    return $this->overrideOwnFactory;
  }
}


