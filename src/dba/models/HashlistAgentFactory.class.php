<?php

/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 02.01.17
 * Time: 23:57
 */

namespace DBA;

class HashlistAgentFactory extends AbstractModelFactory {
  function getModelName() {
    return "HashlistAgent";
  }
  
  function getModelTable() {
    return "HashlistAgent";
  }
  
  function isCachable() {
    return false;
  }
  
  function getCacheValidTime() {
    return -1;
  }

  /**
   * @return HashlistAgent
   */
  function getNullObject() {
    $o = new HashlistAgent(-1, null, null);
    return $o;
  }

  /**
   * @param string $pk
   * @param array $dict
   * @return HashlistAgent
   */
  function createObjectFromDict($pk, $dict) {
    $o = new HashlistAgent($pk, $dict['hashlistId'], $dict['agentId']);
    return $o;
  }

  /**
   * @param array $options
   * @param bool $single
   * @return HashlistAgent|HashlistAgent[]
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
      return Util::cast(parent::filter($options, $single), HashlistAgent::class);
    }
    $objects = parent::filter($options, $single);
    if($join){
      return $objects;
    }
    $models = array();
    foreach($objects as $object){
      $models[] = Util::cast($object, HashlistAgent::class);
    }
    return $models;
  }

  /**
   * @param string $pk
   * @return HashlistAgent
   */
  function get($pk) {
    return Util::cast(parent::get($pk), HashlistAgent::class);
  }

  /**
   * @param HashlistAgent $model
   * @return HashlistAgent
   */
  function save($model) {
    return Util::cast(parent::save($model), HashlistAgent::class);
  }
}