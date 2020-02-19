<?php

namespace DBA;

class AccessGroupAgentFactory extends AbstractModelFactory {
  function getModelName() {
    return "AccessGroupAgent";
  }
  
  function getModelTable() {
    return "AccessGroupAgent";
  }
  
  function isCachable() {
    return false;
  }
  
  function getCacheValidTime() {
    return -1;
  }
  
  /**
   * @return AccessGroupAgent
   */
  function getNullObject() {
    $o = new AccessGroupAgent(-1, null, null);
    return $o;
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return AccessGroupAgent
   */
  function createObjectFromDict($pk, $dict) {
    $o = new AccessGroupAgent($dict['accessGroupAgentId'], $dict['accessGroupId'], $dict['agentId']);
    return $o;
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return AccessGroupAgent|AccessGroupAgent[]
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
      return Util::cast(parent::filter($options, $single), AccessGroupAgent::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, AccessGroupAgent::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return AccessGroupAgent
   */
  function get($pk) {
    return Util::cast(parent::get($pk), AccessGroupAgent::class);
  }
  
  /**
   * @param AccessGroupAgent $model
   * @return AccessGroupAgent
   */
  function save($model) {
    return Util::cast(parent::save($model), AccessGroupAgent::class);
  }
}