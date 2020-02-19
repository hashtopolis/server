<?php

namespace DBA;

class ConfigFactory extends AbstractModelFactory {
  function getModelName() {
    return "Config";
  }
  
  function getModelTable() {
    return "Config";
  }
  
  function isCachable() {
    return false;
  }
  
  function getCacheValidTime() {
    return -1;
  }
  
  /**
   * @return Config
   */
  function getNullObject() {
    $o = new Config(-1, null, null, null);
    return $o;
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return Config
   */
  function createObjectFromDict($pk, $dict) {
    $o = new Config($dict['configId'], $dict['configSectionId'], $dict['item'], $dict['value']);
    return $o;
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return Config|Config[]
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
      return Util::cast(parent::filter($options, $single), Config::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, Config::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return Config
   */
  function get($pk) {
    return Util::cast(parent::get($pk), Config::class);
  }
  
  /**
   * @param Config $model
   * @return Config
   */
  function save($model) {
    return Util::cast(parent::save($model), Config::class);
  }
}