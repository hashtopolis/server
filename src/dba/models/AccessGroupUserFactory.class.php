<?php

namespace DBA;

class AccessGroupUserFactory extends AbstractModelFactory {
  function getModelName() {
    return "AccessGroupUser";
  }
  
  function getModelTable() {
    return "AccessGroupUser";
  }
  
  function isCachable() {
    return false;
  }
  
  function getCacheValidTime() {
    return -1;
  }
  
  /**
   * @return AccessGroupUser
   */
  function getNullObject() {
    $o = new AccessGroupUser(-1, null, null);
    return $o;
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return AccessGroupUser
   */
  function createObjectFromDict($pk, $dict) {
    $o = new AccessGroupUser($dict['accessGroupUserId'], $dict['accessGroupId'], $dict['userId']);
    return $o;
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return AccessGroupUser|AccessGroupUser[]
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
      return Util::cast(parent::filter($options, $single), AccessGroupUser::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, AccessGroupUser::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return AccessGroupUser
   */
  function get($pk) {
    return Util::cast(parent::get($pk), AccessGroupUser::class);
  }
  
  /**
   * @param AccessGroupUser $model
   * @return AccessGroupUser
   */
  function save($model) {
    return Util::cast(parent::save($model), AccessGroupUser::class);
  }
}