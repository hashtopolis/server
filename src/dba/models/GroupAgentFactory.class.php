<?php

namespace DBA;

class GroupAgentFactory extends AbstractModelFactory {
  function getModelName() {
    return "GroupAgent";
  }
  
  function getModelTable() {
    return "GroupAgent";
  }
  
  function isCachable() {
    return false;
  }
  
  function getCacheValidTime() {
    return -1;
  }

  /**
   * @return GroupAgent
   */
  function getNullObject() {
    $o = new GroupAgent(-1, null, null);
    return $o;
  }

  /**
   * @param string $pk
   * @param array $dict
   * @return GroupAgent
   */
  function createObjectFromDict($pk, $dict) {
    $o = new GroupAgent($dict['groupAgentId'], $dict['groupId'], $dict['agentId']);
    return $o;
  }

  /**
   * @param array $options
   * @param bool $single
   * @return GroupAgent|GroupAgent[]
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
      return Util::cast(parent::filter($options, $single), GroupAgent::class);
    }
    $objects = parent::filter($options, $single);
    if($join){
      return $objects;
    }
    $models = array();
    foreach($objects as $object){
      $models[] = Util::cast($object, GroupAgent::class);
    }
    return $models;
  }

  /**
   * @param string $pk
   * @return GroupAgent
   */
  function get($pk) {
    return Util::cast(parent::get($pk), GroupAgent::class);
  }

  /**
   * @param GroupAgent $model
   * @return GroupAgent
   */
  function save($model) {
    return Util::cast(parent::save($model), GroupAgent::class);
  }
}