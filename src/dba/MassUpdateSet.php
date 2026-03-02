<?php

namespace Hashtopolis\dba;

class MassUpdateSet {
  private string $matchValue;
  private mixed $updateValue;
  
  function __construct(string $matchValue, mixed $updateValue) {
    $this->matchValue = $matchValue;
    $this->updateValue = $updateValue;
  }
  
  function getMatchValue(): string {
    return $this->matchValue;
  }
  
  function getUpdateValue(): mixed {
    return $this->updateValue;
  }
  
  function getMassQuery($key): string {
    return "WHEN " . $key . " = ? THEN ? ";
  }
}