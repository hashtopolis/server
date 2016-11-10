<?php

class ChunkFactory extends AbstractModelFactory {
  function getModelName() {
    return "Chunk";
  }
  
  function getModelTable() {
    return "Chunk";
  }
  
  function isCachable() {
    return false;
  }
  
  function getCacheValidTime() {
    return -1;
  }
  
  function getNullObject() {
    $o = new Chunk(-1, null, null, null, null, null, null, null, null, null, null);
    return $o;
  }
  
  function createObjectFromDict($pk, $dict) {
    $o = new Chunk($pk, $dict['taskId'], $dict['skip'], $dict['length'], $dict['agentId'], $dict['dispatchTime'], $dict['progress'], $dict['rprogress'], $dict['state'], $dict['cracked'], $dict['solveTime']);
    return $o;
  }
}