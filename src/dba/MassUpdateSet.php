<?php

namespace Hashtopolis\dba;

class MassUpdateSet {
  private mixed $matchValue;
  private mixed $updateValue;
  
  function __construct(mixed $matchValue, mixed $updateValue) {
    $this->matchValue = $matchValue;
    $this->updateValue = $updateValue;
  }
  
  function getMatchValue(): mixed {
    return $this->matchValue;
  }
  
  function getUpdateValue(): mixed {
    return $this->updateValue;
  }
  
  function getMassQuery($key): string {
    return "WHEN " . $key . " = ? THEN ? ";
  }
}