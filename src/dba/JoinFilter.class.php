<?php

/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 02.01.17
 * Time: 23:57
 */

namespace DBA;

class JoinFilter extends Join  {
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
   * JoinFilter constructor.
   * @param $otherFactory AbstractModelFactory
   * @param $matching1 string
   * @param $matching2 string
   */
  function __construct($otherFactory, $matching1, $matching2) {
    $this->otherFactory = $otherFactory;
    $this->match1 = $matching1;
    $this->match2 = $matching2;
    
    $this->otherTableName = $this->otherFactory->getModelTable();
  }
  
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
}


