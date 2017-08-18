<?php

namespace DBA;

class BinaryFactory extends AbstractModelFactory {
  function getModelName() {
    return "Binary";
  }
  
  function getModelTable() {
    return "Binary";
  }
  
  function isCachable() {
    return false;
  }
  
  function getCacheValidTime() {
    return -1;
  }

  /**
   * @return Binary
   */
  function getNullObject() {
    $o = new Binary(-1, null, null, null);
    return $o;
  }

  /**
   * @param string $pk
   * @param array $dict
   * @return Binary
   */
  function createObjectFromDict($pk, $dict) {
    $o = new Binary($dict['binaryId'], $dict['binaryTypeId'], $dict['version'], $dict['platform']);
    return $o;
  }

  /**
   * @param array $options
   * @param bool $single
   * @return Binary|Binary[]
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
      return Util::cast(parent::filter($options, $single), Binary::class);
    }
    $objects = parent::filter($options, $single);
    if($join){
      return $objects;
    }
    $models = array();
    foreach($objects as $object){
      $models[] = Util::cast($object, Binary::class);
    }
    return $models;
  }

  /**
   * @param string $pk
   * @return Binary
   */
  function get($pk) {
    return Util::cast(parent::get($pk), Binary::class);
  }

  /**
   * @param Binary $model
   * @return Binary
   */
  function save($model) {
    return Util::cast(parent::save($model), Binary::class);
  }
}