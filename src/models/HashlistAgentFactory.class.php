<?php

class HashlistAgentFactory extends AbstractModelFactory {
  function getModelName() {
    return "HashlistAgent";
  }
  
  function getModelTable() {
    return "HashlistAgent";
  }
  
  function isCachable() {
    return false;
  }
  
  function getCacheValidTime() {
    return -1;
  }
  
  function getNullObject() {
    $o = new HashlistAgent(-1, null, null);
    return $o;
  }
  
  function createObjectFromDict($pk, $dict) {
    $o = new HashlistAgent($pk, $dict['hashlistId'], $dict['agentId']);
    return $o;
  }
}