<?php

namespace DBA;

class HashlistHashlistFactory extends AbstractModelFactory {
  function getModelName() {
    return "HashlistHashlist";
  }
  
  function getModelTable() {
    return "HashlistHashlist";
  }
  
  function isCachable() {
    return false;
  }
  
  function getCacheValidTime() {
    return -1;
  }
  
  /**
   * @return HashlistHashlist
   */
  function getNullObject() {
    $o = new HashlistHashlist(-1, null, null);
    return $o;
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return HashlistHashlist
   */
  function createObjectFromDict($pk, $dict) {
    $o = new HashlistHashlist($dict['hashlistHashlistId'], $dict['parentHashlistId'], $dict['hashlistId']);
    return $o;
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return HashlistHashlist|HashlistHashlist[]
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
      return Util::cast(parent::filter($options, $single), HashlistHashlist::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, HashlistHashlist::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return HashlistHashlist
   */
  function get($pk) {
    return Util::cast(parent::get($pk), HashlistHashlist::class);
  }
  
  /**
   * @param HashlistHashlist $model
   * @return HashlistHashlist
   */
  function save($model) {
    return Util::cast(parent::save($model), HashlistHashlist::class);
  }
}