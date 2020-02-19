<?php

namespace DBA;

class TaskDebugOutputFactory extends AbstractModelFactory {
  function getModelName() {
    return "TaskDebugOutput";
  }
  
  function getModelTable() {
    return "TaskDebugOutput";
  }
  
  function isCachable() {
    return false;
  }
  
  function getCacheValidTime() {
    return -1;
  }
  
  /**
   * @return TaskDebugOutput
   */
  function getNullObject() {
    $o = new TaskDebugOutput(-1, null, null);
    return $o;
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return TaskDebugOutput
   */
  function createObjectFromDict($pk, $dict) {
    $o = new TaskDebugOutput($dict['taskDebugOutputId'], $dict['taskId'], $dict['output']);
    return $o;
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return TaskDebugOutput|TaskDebugOutput[]
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
      return Util::cast(parent::filter($options, $single), TaskDebugOutput::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, TaskDebugOutput::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return TaskDebugOutput
   */
  function get($pk) {
    return Util::cast(parent::get($pk), TaskDebugOutput::class);
  }
  
  /**
   * @param TaskDebugOutput $model
   * @return TaskDebugOutput
   */
  function save($model) {
    return Util::cast(parent::save($model), TaskDebugOutput::class);
  }
}