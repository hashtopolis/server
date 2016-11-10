<?php

class HashFactory extends AbstractModelFactory {
  function getModelName() {
    return "Hash";
  }
  
  function getModelTable() {
    return "Hash";
  }
  
  function isCachable() {
    return false;
  }
  
  function getCacheValidTime() {
    return -1;
  }
  
  function getNullObject() {
    $o = new Hash(-1, null, null, null, null, null, null);
    return $o;
  }
  
  function createObjectFromDict($pk, $dict) {
    $o = new Hash($pk, $dict['hashlistId'], $dict['hash'], $dict['salt'], $dict['plaintext'], $dict['time'], $dict['chunkId']);
    return $o;
  }
}