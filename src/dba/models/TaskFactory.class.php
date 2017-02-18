<?php

/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 02.01.17
 * Time: 23:57
 */

namespace DBA;

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

  /**
   * @return Task
   */
  function getNullObject() {
    $o = new Task(-1, null, null, null, null, null, null, null, null, null, null, null);
    return $o;
  }

  /**
   * @param string $pk
   * @param array $dict
   * @return Task
   */
  function createObjectFromDict($pk, $dict) {
    $o = new Task($pk, $dict['taskName'], $dict['attackCmd'], $dict['hashlistId'], $dict['chunkTime'], $dict['statusTimer'], $dict['keyspace'], $dict['progress'], $dict['priority'], $dict['color'], $dict['isSmall'], $dict['isCpuTask']);
    return $o;
  }

  /**
   * @param array $options
   * @param bool $single
   * @return Task|Task[]
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
      return Util::cast(parent::filter($options, $single), Task::class);
    }
    $objects = parent::filter($options, $single);
    if($join){
      return $objects;
    }
    $models = array();
    foreach($objects as $object){
      $models[] = Util::cast($object, Task::class);
    }
    return $models;
  }

  /**
   * @param string $pk
   * @return Task
   */
  function get($pk) {
    return Util::cast(parent::get($pk), Task::class);
  }

  /**
   * @param Task $model
   * @return Task
   */
  function save($model) {
    return Util::cast(parent::save($model), Task::class);
  }
}