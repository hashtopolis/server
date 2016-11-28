<?php

class JoinFilter {
  private $otherFactory;
  private $match1;
  private $match2;
  private $otherTableName;
  private $overrideOwnFactory;
  
  function __construct($otherFactory, $matching1, $matching2, $overrideOwnFactory = null) {
    $this->otherFactory = $otherFactory;
    $this->match1 = $matching1;
    $this->match2 = $matching2;
    
    $this->otherTableName = $this->otherFactory->getModelTable();
    $this->overrideOwnFactory = $overrideOwnFactory;
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
  
  function getOverrideOwnFactory(){
    return $this->overrideOwnFactory;
  }
}

