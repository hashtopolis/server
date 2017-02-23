<?php

/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 23.02.17
 * Time: 20:11
 */
class MassUpdateSet {
  private $matchValue;
  private $updateValue;
  
  function __construct($matchValue, $updateValue) {
    $this->matchValue = $matchValue;
    $this->updateValue = $updateValue;
  }
  
  function getMatchValue() {
    return $this->matchValue;
  }
  
  function getUpdateValue() {
    return $this->updateValue;
  }
  
  function getMassQuery($key) {
    return "WHEN " . $key . " = ? THEN ?";
  }
}