<?php

namespace DBA;

class TaskTaskFactory extends AbstractModelFactory {
  function getModelName() {
    return "TaskTask";
  }
  
  function getModelTable() {
    return "TaskTask";
  }
  
  function isCachable() {
    return false;
  }
  
  function getCacheValidTime() {
    return -1;
  }

  /**
   * @return TaskTask
   */
  function getNullObject() {
    $o = new TaskTask(-1, null, null);
    return $o;
  }

  /**
   * @param string $pk
   * @param array $dict
   * @return TaskTask
   */
  function createObjectFromDict($pk, $dict) {
    $o = new TaskTask($dict['taskTaskId'], $dict['taskId'], $dict['subtaskId']);
    return $o;
  }

  /**
   * @param array $options
   * @param bool $single
   * @return TaskTask|TaskTask[]
   */
  function filter($options, $single = false) {
    $join = false;
    if (array_key_exists('join', $options)) {
      $join = true;
    }
    if($single){
      if($join){
        return parent::filter($options, $single);
      }
      return Util::cast(parent::filter($options, $single), TaskTask::class);
    }
    $objects = parent::filter($options, $single);
    if($join){
      return $objects;
    }
    $models = array();
    foreach($objects as $object){
      $models[] = Util::cast($object, TaskTask::class);
    }
    return $models;
  }

  /**
   * @param string $pk
   * @return TaskTask
   */
  function get($pk) {
    return Util::cast(parent::get($pk), TaskTask::class);
  }

  /**
   * @param TaskTask $model
   * @return TaskTask
   */
  function save($model) {
    return Util::cast(parent::save($model), TaskTask::class);
  }
}