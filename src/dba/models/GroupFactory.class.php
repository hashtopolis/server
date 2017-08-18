<?php

namespace DBA;

class GroupFactory extends AbstractModelFactory {
  function getModelName() {
    return "Group";
  }
  
  function getModelTable() {
    return "Group";
  }
  
  function isCachable() {
    return false;
  }
  
  function getCacheValidTime() {
    return -1;
  }

  /**
   * @return Group
   */
  function getNullObject() {
    $o = new Group(-1, null);
    return $o;
  }

  /**
   * @param string $pk
   * @param array $dict
   * @return Group
   */
  function createObjectFromDict($pk, $dict) {
    $o = new Group($dict['groupId'], $dict['groupName']);
    return $o;
  }

  /**
   * @param array $options
   * @param bool $single
   * @return Group|Group[]
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
      return Util::cast(parent::filter($options, $single), Group::class);
    }
    $objects = parent::filter($options, $single);
    if($join){
      return $objects;
    }
    $models = array();
    foreach($objects as $object){
      $models[] = Util::cast($object, Group::class);
    }
    return $models;
  }

  /**
   * @param string $pk
   * @return Group
   */
  function get($pk) {
    return Util::cast(parent::get($pk), Group::class);
  }

  /**
   * @param Group $model
   * @return Group
   */
  function save($model) {
    return Util::cast(parent::save($model), Group::class);
  }
}