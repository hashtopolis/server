<?php

namespace DBA;

class FileTaskFactory extends AbstractModelFactory {
  function getModelName() {
    return "FileTask";
  }
  
  function getModelTable() {
    return "FileTask";
  }
  
  function isCachable() {
    return false;
  }
  
  function getCacheValidTime() {
    return -1;
  }
  
  /**
   * @return FileTask
   */
  function getNullObject() {
    $o = new FileTask(-1, null, null);
    return $o;
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return FileTask
   */
  function createObjectFromDict($pk, $dict) {
    $o = new FileTask($dict['fileTaskId'], $dict['fileId'], $dict['taskId']);
    return $o;
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return FileTask|FileTask[]
   */
  function filter($options, $single = false) {
    $join = false;
    if (array_key_exists('join', $options)) {
      $join = true;
    }
    if ($single) {
      if ($join) {
        return parent::filter($options, $single);
      }
      return Util::cast(parent::filter($options, $single), FileTask::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, FileTask::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return FileTask
   */
  function get($pk) {
    return Util::cast(parent::get($pk), FileTask::class);
  }
  
  /**
   * @param FileTask $model
   * @return FileTask
   */
  function save($model) {
    return Util::cast(parent::save($model), FileTask::class);
  }
}