<?php

/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 02.01.17
 * Time: 23:57
 */

namespace DBA;

class AgentErrorFactory extends AbstractModelFactory {
  function getModelName() {
    return "AgentError";
  }
  
  function getModelTable() {
    return "AgentError";
  }
  
  function isCachable() {
    return false;
  }
  
  function getCacheValidTime() {
    return -1;
  }

  /**
   * @return AgentError
   */
  function getNullObject() {
    $o = new AgentError(-1, null, null, null, null);
    return $o;
  }

  /**
   * @param string $pk
   * @param array $dict
   * @return AgentError
   */
  function createObjectFromDict($pk, $dict) {
    $o = new AgentError($pk, $dict['agentId'], $dict['taskId'], $dict['time'], $dict['error']);
    return $o;
  }

  /**
   * @param array $options
   * @param bool $single
   * @return AgentError|AgentError[]
   */
  function filter($options, $single = false) {
    if($single){
      return Util::cast(parent::filter($options, $single), AgentError::class);
    }
    $objects = parent::filter($options, $single);
    $models = array();
    foreach($objects as $object){
      $models[] = Util::cast($object, AgentError::class);
    }
    return $models;
  }

  /**
   * @param string $pk
   * @return AgentError
   */
  function get($pk) {
    return Util::cast(parent::get($pk), AgentError::class);
  }

  /**
   * @param AgentError $model
   * @return AgentError
   */
  function save($model) {
    return Util::cast(parent::save($model), AgentError::class);
  }
}