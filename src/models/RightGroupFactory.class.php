<?php

class RightGroupFactory extends AbstractModelFactory {
  function getModelName() {
    return "RightGroup";
  }
  
  function getModelTable() {
    return "RightGroup";
  }
  
  function isCachable() {
    return false;
  }
  
  function getCacheValidTime() {
    return -1;
  }
  
  function getNullObject() {
    $o = new RightGroup(-1, null, null);
    return $o;
  }
  
  function createObjectFromDict($pk, $dict) {
    $o = new RightGroup($pk, $dict['groupName'], $dict['level']);
    return $o;
  }
}