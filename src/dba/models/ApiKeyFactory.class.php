<?php

namespace DBA;

class ApiKeyFactory extends AbstractModelFactory {
  function getModelName() {
    return "ApiKey";
  }
  
  function getModelTable() {
    return "ApiKey";
  }
  
  function isCachable() {
    return false;
  }
  
  function getCacheValidTime() {
    return -1;
  }
  
  /**
   * @return ApiKey
   */
  function getNullObject() {
    $o = new ApiKey(-1, null, null, null, null, null, null);
    return $o;
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return ApiKey
   */
  function createObjectFromDict($pk, $dict) {
    $o = new ApiKey($dict['apiKeyId'], $dict['startValid'], $dict['endValid'], $dict['accessKey'], $dict['accessCount'], $dict['userId'], $dict['apiGroupId']);
    return $o;
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return ApiKey|ApiKey[]
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
      return Util::cast(parent::filter($options, $single), ApiKey::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, ApiKey::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return ApiKey
   */
  function get($pk) {
    return Util::cast(parent::get($pk), ApiKey::class);
  }
  
  /**
   * @param ApiKey $model
   * @return ApiKey
   */
  function save($model) {
    return Util::cast(parent::save($model), ApiKey::class);
  }
}