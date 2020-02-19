<?php

namespace DBA;

class SpeedFactory extends AbstractModelFactory {
  function getModelName() {
    return "Speed";
  }
  
  function getModelTable() {
    return "Speed";
  }
  
  function isCachable() {
    return false;
  }
  
  function getCacheValidTime() {
    return -1;
  }
  
  /**
   * @return Speed
   */
  function getNullObject() {
    $o = new Speed(-1, null, null, null, null);
    return $o;
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return Speed
   */
  function createObjectFromDict($pk, $dict) {
    $o = new Speed($dict['speedId'], $dict['agentId'], $dict['taskId'], $dict['speed'], $dict['time']);
    return $o;
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return Speed|Speed[]
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
      return Util::cast(parent::filter($options, $single), Speed::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, Speed::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return Speed
   */
  function get($pk) {
    return Util::cast(parent::get($pk), Speed::class);
  }
  
  /**
   * @param Speed $model
   * @return Speed
   */
  function save($model) {
    return Util::cast(parent::save($model), Speed::class);
  }
}