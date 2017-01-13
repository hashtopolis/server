<?php

/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 02.01.17
 * Time: 23:57
 */

namespace DBA;

class TaskFileFactory extends AbstractModelFactory {
  function getModelName() {
    return "TaskFile";
  }
  
  function getModelTable() {
    return "TaskFile";
  }
  
  function isCachable() {
    return false;
  }
  
  function getCacheValidTime() {
    return -1;
  }

  /**
   * @return TaskFile
   */
  function getNullObject() {
    $o = new TaskFile(-1, null, null);
    return $o;
  }

  /**
   * @param string $pk
   * @param array $dict
   * @return TaskFile
   */
  function createObjectFromDict($pk, $dict) {
    $o = new TaskFile($pk, $dict['taskId'], $dict['fileId']);
    return $o;
  }

  /**
   * @param array $options
   * @param bool $single
   * @return TaskFile|TaskFile[]
   */
  function filter($options, $single = false) {
    if($single){
      return Util::cast(parent::filter($options, $single), TaskFile::class);
    }
    $objects = parent::filter($options, $single);
    $models = array();
    foreach($objects as $object){
      $models[] = Util::cast($object, TaskFile::class);
    }
    return $models;
  }

  /**
   * @param string $pk
   * @return TaskFile
   */
  function get($pk) {
    return Util::cast(parent::get($pk), TaskFile::class);
  }

  /**
   * @param TaskFile $model
   * @return TaskFile
   */
  function save($model) {
    return Util::cast(parent::save($model), TaskFile::class);
  }
}