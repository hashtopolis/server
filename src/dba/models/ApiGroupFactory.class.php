<?php

namespace DBA;

class ApiGroupFactory extends AbstractModelFactory {
  function getModelName() {
    return "ApiGroup";
  }
  
  function getModelTable() {
    return "ApiGroup";
  }
  
  function isCachable() {
    return false;
  }
  
  function getCacheValidTime() {
    return -1;
  }
  
  /**
   * @return ApiGroup
   */
  function getNullObject() {
    $o = new ApiGroup(-1, null, null);
    return $o;
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return ApiGroup
   */
  function createObjectFromDict($pk, $dict) {
    $o = new ApiGroup($dict['apiGroupId'], $dict['permissions'], $dict['name']);
    return $o;
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return ApiGroup|ApiGroup[]
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
      return Util::cast(parent::filter($options, $single), ApiGroup::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, ApiGroup::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return ApiGroup
   */
  function get($pk) {
    return Util::cast(parent::get($pk), ApiGroup::class);
  }
  
  /**
   * @param ApiGroup $model
   * @return ApiGroup
   */
  function save($model) {
    return Util::cast(parent::save($model), ApiGroup::class);
  }
}