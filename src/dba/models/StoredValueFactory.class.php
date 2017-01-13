<?php

/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 02.01.17
 * Time: 23:57
 */

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
    $o = new StoredValue($pk, $dict['val']);
    return $o;
  }

  /**
   * @param array $options
   * @param bool $single
   * @return StoredValue|StoredValue[]
   */
  function filter($options, $single = false) {
    if($single){
      return Util::cast(parent::filter($options, $single), StoredValue::class);
    }
    $objects = parent::filter($options, $single);
    $models = array();
    foreach($objects as $object){
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