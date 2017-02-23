<?php
/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 23.02.17
 * Time: 20:11
 */

class MassUpdateSet {
  private $key;
  private $matchValue;
  private $updateValue;
  
  function __construct($key, $matchValue, $updateValue) {
    $this->key = $key;
    $this->matchValue = $matchValue;
    $this->updateValue = $updateValue;
  }
  
  function getMatchValue() {
    return $this->matchValue;
  }
  
  function getUpdateValue() {
    return $this->updateValue;
  }
  
  function getMassQuery($table = ""){
    return "WHEN ".$table. $this->key ." = ? THEN ?";
  }
}