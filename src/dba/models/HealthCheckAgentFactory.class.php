<?php

namespace DBA;

class HealthCheckAgentFactory extends AbstractModelFactory {
  function getModelName() {
    return "HealthCheckAgent";
  }
  
  function getModelTable() {
    return "HealthCheckAgent";
  }
  
  function isCachable() {
    return false;
  }
  
  function getCacheValidTime() {
    return -1;
  }
  
  /**
   * @return HealthCheckAgent
   */
  function getNullObject() {
    $o = new HealthCheckAgent(-1, null, null, null, null, null, null, null, null);
    return $o;
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return HealthCheckAgent
   */
  function createObjectFromDict($pk, $dict) {
    $o = new HealthCheckAgent($dict['healthCheckAgentId'], $dict['healthCheckId'], $dict['agentId'], $dict['status'], $dict['cracked'], $dict['numGpus'], $dict['start'], $dict['end'], $dict['errors']);
    return $o;
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return HealthCheckAgent|HealthCheckAgent[]
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
      return Util::cast(parent::filter($options, $single), HealthCheckAgent::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, HealthCheckAgent::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return HealthCheckAgent
   */
  function get($pk) {
    return Util::cast(parent::get($pk), HealthCheckAgent::class);
  }
  
  /**
   * @param HealthCheckAgent $model
   * @return HealthCheckAgent
   */
  function save($model) {
    return Util::cast(parent::save($model), HealthCheckAgent::class);
  }
}