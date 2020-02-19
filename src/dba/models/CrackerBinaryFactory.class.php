<?php

namespace DBA;

class CrackerBinaryFactory extends AbstractModelFactory {
  function getModelName() {
    return "CrackerBinary";
  }
  
  function getModelTable() {
    return "CrackerBinary";
  }
  
  function isCachable() {
    return false;
  }
  
  function getCacheValidTime() {
    return -1;
  }
  
  /**
   * @return CrackerBinary
   */
  function getNullObject() {
    $o = new CrackerBinary(-1, null, null, null, null);
    return $o;
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return CrackerBinary
   */
  function createObjectFromDict($pk, $dict) {
    $o = new CrackerBinary($dict['crackerBinaryId'], $dict['crackerBinaryTypeId'], $dict['version'], $dict['downloadUrl'], $dict['binaryName']);
    return $o;
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return CrackerBinary|CrackerBinary[]
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
      return Util::cast(parent::filter($options, $single), CrackerBinary::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, CrackerBinary::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return CrackerBinary
   */
  function get($pk) {
    return Util::cast(parent::get($pk), CrackerBinary::class);
  }
  
  /**
   * @param CrackerBinary $model
   * @return CrackerBinary
   */
  function save($model) {
    return Util::cast(parent::save($model), CrackerBinary::class);
  }
}