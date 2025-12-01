<?php

namespace DBA;

class LikeFilter extends Filter {
  private $key;
  private $value;
  private $match;
  /**
   * @var AbstractModelFactory
   */
  private $overrideFactory;
  
  function __construct($key, $value, $overrideFactory = null) {
    $this->key = $key;
    $this->value = $value;
    $this->overrideFactory = $overrideFactory;
    $this->match = true;
  }
  
  function setMatch($status) {
    $this->match = $status;
  }
  
  function getQueryString(AbstractModelFactory $factory, bool $includeTable = false): string {
    if ($this->overrideFactory != null) {
      $factory = $this->overrideFactory;
    }
    $table = "";
    if ($includeTable) {
      $table = $factory->getMappedModelTable() . ".";
    }
    
    $inv = "";
    if ($this->match === false) {
      $inv = " NOT";
    }
    
    // it is not ideal to have to make a distinction between the DB types here, but currently there does not seem to be another solution to achieve real case-sensitive like filtering
    $likeStatement = " BINARY LIKE ?";
    if (DBA_TYPE == 'postgres') {
      $likeStatement = " LIKE ? COLLATE \"C\"";
    }
    return $table . AbstractModelFactory::getMappedModelKey($factory->getNullObject(), $this->key) . $inv . $likeStatement;
  }
  
  function getValue() {
    return $this->value;
  }
  
  function getHasValue(): bool {
    return true;
  }
}
