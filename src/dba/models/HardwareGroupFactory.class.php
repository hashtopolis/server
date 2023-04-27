<?php

namespace DBA;

class HardwareGroupFactory extends AbstractModelFactory {
  function getModelName() {
    return "HardwareGroup";
  }
  
  function getModelTable() {
    return "HardwareGroup";
  }
  
  function isCachable() {
    return false;
  }
  
  function getCacheValidTime() {
    return -1;
  }
  
  /**
   * @return HardwareGroup
   */
  function getNullObject() {
    $o = new HardwareGroup(-1, null, null);
    return $o;
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return HardwareGroup
   */
  function createObjectFromDict($pk, $dict) {
    $o = new HardwareGroup($dict['hardwareGroupId'], $dict['devices'], $dict['benchmarkId']);
    return $o;
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return HardwareGroup|HardwareGroup[]
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
      return Util::cast(parent::filter($options, $single), HardwareGroup::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, HardwareGroup::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return HardwareGroup
   */
  function get($pk) {
    return Util::cast(parent::get($pk), HardwareGroup::class);
  }
  
  /**
   * @param HardwareGroup $model
   * @return HardwareGroup
   */
  function save($model) {
    return Util::cast(parent::save($model), HardwareGroup::class);
  }
}