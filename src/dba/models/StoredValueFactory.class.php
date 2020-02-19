<?php

namespace DBA;

class StoredValueFactory extends AbstractModelFactory {
  function getModelName() {
    return "StoredValue";
  }
  
  function getModelTable() {
    return "StoredValue";
  }
  
  function isCachable() {
    return false;
  }
  
  function getCacheValidTime() {
    return -1;
  }
  
  /**
   * @return StoredValue
   */
  function getNullObject() {
    $o = new StoredValue(-1, null);
    return $o;
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return StoredValue
   */
  function createObjectFromDict($pk, $dict) {
    $o = new StoredValue($dict['storedValueId'], $dict['val']);
    return $o;
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return StoredValue|StoredValue[]
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
      return Util::cast(parent::filter($options, $single), StoredValue::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, StoredValue::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return StoredValue
   */
  function get($pk) {
    return Util::cast(parent::get($pk), StoredValue::class);
  }
  
  /**
   * @param StoredValue $model
   * @return StoredValue
   */
  function save($model) {
    return Util::cast(parent::save($model), StoredValue::class);
  }
}