<?php

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
    return "WHEN " . $key . " = ? THEN ? ";
  }
}