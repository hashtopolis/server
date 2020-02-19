<?php

namespace DBA;

class HealthCheckFactory extends AbstractModelFactory {
  function getModelName() {
    return "HealthCheck";
  }
  
  function getModelTable() {
    return "HealthCheck";
  }
  
  function isCachable() {
    return false;
  }
  
  function getCacheValidTime() {
    return -1;
  }
  
  /**
   * @return HealthCheck
   */
  function getNullObject() {
    $o = new HealthCheck(-1, null, null, null, null, null, null, null);
    return $o;
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return HealthCheck
   */
  function createObjectFromDict($pk, $dict) {
    $o = new HealthCheck($dict['healthCheckId'], $dict['time'], $dict['status'], $dict['checkType'], $dict['hashtypeId'], $dict['crackerBinaryId'], $dict['expectedCracks'], $dict['attackCmd']);
    return $o;
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return HealthCheck|HealthCheck[]
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
      return Util::cast(parent::filter($options, $single), HealthCheck::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, HealthCheck::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return HealthCheck
   */
  function get($pk) {
    return Util::cast(parent::get($pk), HealthCheck::class);
  }
  
  /**
   * @param HealthCheck $model
   * @return HealthCheck
   */
  function save($model) {
    return Util::cast(parent::save($model), HealthCheck::class);
  }
}