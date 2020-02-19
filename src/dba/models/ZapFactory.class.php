<?php

namespace DBA;

class ZapFactory extends AbstractModelFactory {
  function getModelName() {
    return "Zap";
  }
  
  function getModelTable() {
    return "Zap";
  }
  
  function isCachable() {
    return false;
  }
  
  function getCacheValidTime() {
    return -1;
  }
  
  /**
   * @return Zap
   */
  function getNullObject() {
    $o = new Zap(-1, null, null, null, null);
    return $o;
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return Zap
   */
  function createObjectFromDict($pk, $dict) {
    $o = new Zap($dict['zapId'], $dict['hash'], $dict['solveTime'], $dict['agentId'], $dict['hashlistId']);
    return $o;
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return Zap|Zap[]
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
      return Util::cast(parent::filter($options, $single), Zap::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, Zap::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return Zap
   */
  function get($pk) {
    return Util::cast(parent::get($pk), Zap::class);
  }
  
  /**
   * @param Zap $model
   * @return Zap
   */
  function save($model) {
    return Util::cast(parent::save($model), Zap::class);
  }
}