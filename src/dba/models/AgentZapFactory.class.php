<?php

namespace DBA;

class AgentZapFactory extends AbstractModelFactory {
  function getModelName() {
    return "AgentZap";
  }
  
  function getModelTable() {
    return "AgentZap";
  }
  
  function isCachable() {
    return false;
  }
  
  function getCacheValidTime() {
    return -1;
  }
  
  /**
   * @return AgentZap
   */
  function getNullObject() {
    $o = new AgentZap(-1, null, null);
    return $o;
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return AgentZap
   */
  function createObjectFromDict($pk, $dict) {
    $o = new AgentZap($dict['agentZapId'], $dict['agentId'], $dict['lastZapId']);
    return $o;
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return AgentZap|AgentZap[]
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
      return Util::cast(parent::filter($options, $single), AgentZap::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, AgentZap::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return AgentZap
   */
  function get($pk) {
    return Util::cast(parent::get($pk), AgentZap::class);
  }
  
  /**
   * @param AgentZap $model
   * @return AgentZap
   */
  function save($model) {
    return Util::cast(parent::save($model), AgentZap::class);
  }
}