<?php

/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 02.01.17
 * Time: 23:57
 */

namespace DBA;

class LikeFilter extends Filter {
  private $key;
  private $value;
  /**
   * @var AbstractModelFactory
   */
  private $factory;
  
  function __construct($key, $value, $factory = null) {
    $this->key = $key;
    $this->value = $value;
    $this->factory = $factory;
  }
  
  function getQueryString($table = "") {
    if ($table != "") {
      $table = $table . ".";
    }
    if ($this->factory != null) {
      $table = $this->factory->getModelTable() . ".";
    }
    
    return $table . $this->key . " LIKE ?";
  }
  
  function getValue() {
    return $this->value;
  }
  
  function getHasValue() {
    return true;
  }
}
