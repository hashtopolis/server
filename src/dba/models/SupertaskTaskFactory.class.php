<?php

namespace DBA;

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

  /**
   * @return SupertaskTask
   */
  function getNullObject() {
    $o = new SupertaskTask(-1, null, null);
    return $o;
  }

  /**
   * @param string $pk
   * @param array $dict
   * @return SupertaskTask
   */
  function createObjectFromDict($pk, $dict) {
    $o = new SupertaskTask($dict['supertaskTaskId'], $dict['taskId'], $dict['supertaskId']);
    return $o;
  }

  /**
   * @param array $options
   * @param bool $single
   * @return SupertaskTask|SupertaskTask[]
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
      return Util::cast(parent::filter($options, $single), SupertaskTask::class);
    }
    $objects = parent::filter($options, $single);
    if($join){
      return $objects;
    }
    $models = array();
    foreach($objects as $object){
      $models[] = Util::cast($object, SupertaskTask::class);
    }
    return $models;
  }

  /**
   * @param string $pk
   * @return SupertaskTask
   */
  function get($pk) {
    return Util::cast(parent::get($pk), SupertaskTask::class);
  }

  /**
   * @param SupertaskTask $model
   * @return SupertaskTask
   */
  function save($model) {
    return Util::cast(parent::save($model), SupertaskTask::class);
  }
}