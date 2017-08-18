<?php

namespace DBA;

class GroupUserFactory extends AbstractModelFactory {
  function getModelName() {
    return "GroupUser";
  }
  
  function getModelTable() {
    return "GroupUser";
  }
  
  function isCachable() {
    return false;
  }
  
  function getCacheValidTime() {
    return -1;
  }

  /**
   * @return GroupUser
   */
  function getNullObject() {
    $o = new GroupUser(-1, null, null);
    return $o;
  }

  /**
   * @param string $pk
   * @param array $dict
   * @return GroupUser
   */
  function createObjectFromDict($pk, $dict) {
    $o = new GroupUser($dict['groupUserId'], $dict['groupId'], $dict['userId']);
    return $o;
  }

  /**
   * @param array $options
   * @param bool $single
   * @return GroupUser|GroupUser[]
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
      return Util::cast(parent::filter($options, $single), GroupUser::class);
    }
    $objects = parent::filter($options, $single);
    if($join){
      return $objects;
    }
    $models = array();
    foreach($objects as $object){
      $models[] = Util::cast($object, GroupUser::class);
    }
    return $models;
  }

  /**
   * @param string $pk
   * @return GroupUser
   */
  function get($pk) {
    return Util::cast(parent::get($pk), GroupUser::class);
  }

  /**
   * @param GroupUser $model
   * @return GroupUser
   */
  function save($model) {
    return Util::cast(parent::save($model), GroupUser::class);
  }
}