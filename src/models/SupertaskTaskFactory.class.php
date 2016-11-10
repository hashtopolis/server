<?php

class SupertaskTaskFactory extends AbstractModelFactory {
  function getModelName() {
    return "SupertaskTask";
  }
  
  function getModelTable() {
    return "SupertaskTask";
  }
  
  function isCachable() {
    return false;
  }
  
  function getCacheValidTime() {
    return -1;
  }
  
  function getNullObject() {
    $o = new SupertaskTask(-1, null, null);
    return $o;
  }
  
  function createObjectFromDict($pk, $dict) {
    $o = new SupertaskTask($pk, $dict['taskId'], $dict['supertaskId']);
    return $o;
  }
}