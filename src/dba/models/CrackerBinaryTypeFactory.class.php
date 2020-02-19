<?php

namespace DBA;

class CrackerBinaryTypeFactory extends AbstractModelFactory {
  function getModelName() {
    return "CrackerBinaryType";
  }
  
  function getModelTable() {
    return "CrackerBinaryType";
  }
  
  function isCachable() {
    return false;
  }
  
  function getCacheValidTime() {
    return -1;
  }
  
  /**
   * @return CrackerBinaryType
   */
  function getNullObject() {
    $o = new CrackerBinaryType(-1, null, null);
    return $o;
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return CrackerBinaryType
   */
  function createObjectFromDict($pk, $dict) {
    $o = new CrackerBinaryType($dict['crackerBinaryTypeId'], $dict['typeName'], $dict['isChunkingAvailable']);
    return $o;
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return CrackerBinaryType|CrackerBinaryType[]
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
      return Util::cast(parent::filter($options, $single), CrackerBinaryType::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, CrackerBinaryType::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return CrackerBinaryType
   */
  function get($pk) {
    return Util::cast(parent::get($pk), CrackerBinaryType::class);
  }
  
  /**
   * @param CrackerBinaryType $model
   * @return CrackerBinaryType
   */
  function save($model) {
    return Util::cast(parent::save($model), CrackerBinaryType::class);
  }
}