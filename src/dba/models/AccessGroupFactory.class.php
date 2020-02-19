<?php

namespace DBA;

class AccessGroupFactory extends AbstractModelFactory {
  function getModelName() {
    return "AccessGroup";
  }
  
  function getModelTable() {
    return "AccessGroup";
  }
  
  function isCachable() {
    return false;
  }
  
  function getCacheValidTime() {
    return -1;
  }
  
  /**
   * @return AccessGroup
   */
  function getNullObject() {
    $o = new AccessGroup(-1, null);
    return $o;
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return AccessGroup
   */
  function createObjectFromDict($pk, $dict) {
    $o = new AccessGroup($dict['accessGroupId'], $dict['groupName']);
    return $o;
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return AccessGroup|AccessGroup[]
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
      return Util::cast(parent::filter($options, $single), AccessGroup::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, AccessGroup::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return AccessGroup
   */
  function get($pk) {
    return Util::cast(parent::get($pk), AccessGroup::class);
  }
  
  /**
   * @param AccessGroup $model
   * @return AccessGroup
   */
  function save($model) {
    return Util::cast(parent::save($model), AccessGroup::class);
  }
}