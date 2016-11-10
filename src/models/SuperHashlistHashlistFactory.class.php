<?php

class SuperHashlistHashlistFactory extends AbstractModelFactory {
  function getModelName() {
    return "SuperHashlistHashlist";
  }
  
  function getModelTable() {
    return "SuperHashlistHashlist";
  }
  
  function isCachable() {
    return false;
  }
  
  function getCacheValidTime() {
    return -1;
  }
  
  function getNullObject() {
    $o = new SuperHashlistHashlist(-1, null, null);
    return $o;
  }
  
  function createObjectFromDict($pk, $dict) {
    $o = new SuperHashlistHashlist($pk, $dict['superHashlistId'], $dict['hashlistId']);
    return $o;
  }
}