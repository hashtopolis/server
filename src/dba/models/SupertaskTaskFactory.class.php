<?php

/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 02.01.17
 * Time: 23:57
 */

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
    $o = new SupertaskTask($pk, $dict['taskId'], $dict['supertaskId']);
    return $o;
  }

  /**
   * @param array $options
   * @param bool $single
   * @return SupertaskTask|SupertaskTask[]
   */
  function filter($options, $single = false) {
    if($single){
      return Util::cast(parent::filter($options, $single), SupertaskTask::class);
    }
    $objects = parent::filter($options, $single);
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