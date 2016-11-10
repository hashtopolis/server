<?php

class HashTypeFactory extends AbstractModelFactory {
  function getModelName() {
    return "HashType";
  }
  
  function getModelTable() {
    return "HashType";
  }
  
  function isCachable() {
    return false;
  }
  
  function getCacheValidTime() {
    return -1;
  }
  
  function getNullObject() {
    $o = new HashType(-1, null);
    return $o;
  }
  
  function createObjectFromDict($pk, $dict) {
    $o = new HashType($pk, $dict['description']);
    return $o;
  }
}