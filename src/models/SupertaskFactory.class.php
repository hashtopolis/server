<?php

class SupertaskFactory extends AbstractModelFactory {
  function getModelName() {
    return "Supertask";
  }
  
  function getModelTable() {
    return "Supertask";
  }
  
  function isCachable() {
    return false;
  }
  
  function getCacheValidTime() {
    return -1;
  }
  
  function getNullObject() {
    $o = new Supertask(-1, null);
    return $o;
  }
  
  function createObjectFromDict($pk, $dict) {
    $o = new Supertask($pk, $dict['supertaskName']);
    return $o;
  }
}