<?php

namespace DBA;

class BinaryTypeFactory extends AbstractModelFactory {
  function getModelName() {
    return "BinaryType";
  }
  
  function getModelTable() {
    return "BinaryType";
  }
  
  function isCachable() {
    return false;
  }
  
  function getCacheValidTime() {
    return -1;
  }

  /**
   * @return BinaryType
   */
  function getNullObject() {
    $o = new BinaryType(-1, null, null);
    return $o;
  }

  /**
   * @param string $pk
   * @param array $dict
   * @return BinaryType
   */
  function createObjectFromDict($pk, $dict) {
    $o = new BinaryType($dict['binaryTypeId'], $dict['typeName'], $dict['isChunkingAvailable']);
    return $o;
  }

  /**
   * @param array $options
   * @param bool $single
   * @return BinaryType|BinaryType[]
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
      return Util::cast(parent::filter($options, $single), BinaryType::class);
    }
    $objects = parent::filter($options, $single);
    if($join){
      return $objects;
    }
    $models = array();
    foreach($objects as $object){
      $models[] = Util::cast($object, BinaryType::class);
    }
    return $models;
  }

  /**
   * @param string $pk
   * @return BinaryType
   */
  function get($pk) {
    return Util::cast(parent::get($pk), BinaryType::class);
  }

  /**
   * @param BinaryType $model
   * @return BinaryType
   */
  function save($model) {
    return Util::cast(parent::save($model), BinaryType::class);
  }
}