<?php

/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 02.01.17
 * Time: 23:57
 */

namespace DBA;

class RightGroupFactory extends AbstractModelFactory {
  function getModelName() {
    return "RightGroup";
  }
  
  function getModelTable() {
    return "RightGroup";
  }
  
  function isCachable() {
    return false;
  }
  
  function getCacheValidTime() {
    return -1;
  }

  /**
   * @return RightGroup
   */
  function getNullObject() {
    $o = new RightGroup(-1, null, null);
    return $o;
  }

  /**
   * @param string $pk
   * @param array $dict
   * @return RightGroup
   */
  function createObjectFromDict($pk, $dict) {
    $o = new RightGroup($pk, $dict['groupName'], $dict['level']);
    return $o;
  }

  /**
   * @param array $options
   * @param bool $single
   * @return RightGroup|RightGroup[]
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
      return Util::cast(parent::filter($options, $single), RightGroup::class);
    }
    $objects = parent::filter($options, $single);
    $models = array();
    foreach($objects as $object){
      if($join){
        $models[] = $object;
      }
      else{
        $models[] = Util::cast($object, RightGroup::class);
      }
    }
    return $models;
  }

  /**
   * @param string $pk
   * @return RightGroup
   */
  function get($pk) {
    return Util::cast(parent::get($pk), RightGroup::class);
  }

  /**
   * @param RightGroup $model
   * @return RightGroup
   */
  function save($model) {
    return Util::cast(parent::save($model), RightGroup::class);
  }
}