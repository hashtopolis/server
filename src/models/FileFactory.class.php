<?php

class FileFactory extends AbstractModelFactory {
  function getModelName() {
    return "File";
  }
  
  function getModelTable() {
    return "File";
  }
  
  function isCachable() {
    return false;
  }
  
  function getCacheValidTime() {
    return -1;
  }
  
  function getNullObject() {
    $o = new File(-1, null, null, null, null);
    return $o;
  }
  
  function createObjectFromDict($pk, $dict) {
    $o = new File($pk, $dict['filename'], $dict['size'], $dict['secret'], $dict['fileType']);
    return $o;
  }
}