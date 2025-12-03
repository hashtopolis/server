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
   * JoinFilter constructor.
   * @param $otherFactory AbstractModelFactory
   * @param $matching1 string
   * @param $matching2 string
   * @param $overrideOwnFactory AbstractModelFactory
   */
  function __construct($otherFactory, $matching1, $matching2, $overrideOwnFactory = null) {
    $this->otherFactory = $otherFactory;
    $this->match1 = $matching1;
    $this->match2 = $matching2;
    
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
  
  /**
   * @return AbstractModelFactory
   */
  function getOverrideOwnFactory() {
    return $this->overrideOwnFactory;
  }
}


