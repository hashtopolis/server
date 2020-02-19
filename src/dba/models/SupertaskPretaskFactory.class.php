<?php

namespace DBA;

class SupertaskPretaskFactory extends AbstractModelFactory {
  function getModelName() {
    return "SupertaskPretask";
  }
  
  function getModelTable() {
    return "SupertaskPretask";
  }
  
  function isCachable() {
    return false;
  }
  
  function getCacheValidTime() {
    return -1;
  }
  
  /**
   * @return SupertaskPretask
   */
  function getNullObject() {
    $o = new SupertaskPretask(-1, null, null);
    return $o;
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return SupertaskPretask
   */
  function createObjectFromDict($pk, $dict) {
    $o = new SupertaskPretask($dict['supertaskPretaskId'], $dict['supertaskId'], $dict['pretaskId']);
    return $o;
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return SupertaskPretask|SupertaskPretask[]
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
      return Util::cast(parent::filter($options, $single), SupertaskPretask::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, SupertaskPretask::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return SupertaskPretask
   */
  function get($pk) {
    return Util::cast(parent::get($pk), SupertaskPretask::class);
  }
  
  /**
   * @param SupertaskPretask $model
   * @return SupertaskPretask
   */
  function save($model) {
    return Util::cast(parent::save($model), SupertaskPretask::class);
  }
}