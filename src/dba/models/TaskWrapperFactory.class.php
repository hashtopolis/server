<?php

namespace DBA;

class TaskWrapperFactory extends AbstractModelFactory {
  function getModelName() {
    return "TaskWrapper";
  }
  
  function getModelTable() {
    return "TaskWrapper";
  }
  
  function isCachable() {
    return false;
  }
  
  function getCacheValidTime() {
    return -1;
  }
  
  /**
   * @return TaskWrapper
   */
  function getNullObject() {
    $o = new TaskWrapper(-1, null, null, null, null, null, null, null, null);
    return $o;
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return TaskWrapper
   */
  function createObjectFromDict($pk, $dict) {
    $o = new TaskWrapper($dict['taskWrapperId'], $dict['priority'], $dict['maxAgents'], $dict['taskType'], $dict['hashlistId'], $dict['accessGroupId'], $dict['taskWrapperName'], $dict['isArchived'], $dict['cracked']);
    return $o;
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return TaskWrapper|TaskWrapper[]
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
      return Util::cast(parent::filter($options, $single), TaskWrapper::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, TaskWrapper::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return TaskWrapper
   */
  function get($pk) {
    return Util::cast(parent::get($pk), TaskWrapper::class);
  }
  
  /**
   * @param TaskWrapper $model
   * @return TaskWrapper
   */
  function save($model) {
    return Util::cast(parent::save($model), TaskWrapper::class);
  }
}