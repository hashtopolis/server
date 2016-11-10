<?php

class TaskFactory extends AbstractModelFactory {
  function getModelName() {
    return "Task";
  }
  
  function getModelTable() {
    return "Task";
  }
  
  function isCachable() {
    return false;
  }
  
  function getCacheValidTime() {
    return -1;
  }
  
  function getNullObject() {
    $o = new Task(-1, null, null, null, null, null, null, null, null, null, null, null, null);
    return $o;
  }
  
  function createObjectFromDict($pk, $dict) {
    $o = new Task($pk, $dict['taskName'], $dict['attackCmd'], $dict['hashlistId'], $dict['chunkTime'], $dict['statusTimer'], $dict['autoAdjust'], $dict['keyspace'], $dict['progress'], $dict['priority'], $dict['color'], $dict['isSmall'], $dict['isCpuTask']);
    return $o;
  }
}