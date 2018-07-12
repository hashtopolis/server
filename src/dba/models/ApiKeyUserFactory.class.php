<?php

namespace DBA;

class ApiKeyUserFactory extends AbstractModelFactory {
  function getModelName() {
    return "ApiKeyUser";
  }
  
  function getModelTable() {
    return "ApiKeyUser";
  }
  
  function isCachable() {
    return false;
  }
  
  function getCacheValidTime() {
    return -1;
  }

  /**
   * @return ApiKeyUser
   */
  function getNullObject() {
    $o = new ApiKeyUser(-1, null, null);
    return $o;
  }

  /**
   * @param string $pk
   * @param array $dict
   * @return ApiKeyUser
   */
  function createObjectFromDict($pk, $dict) {
    $o = new ApiKeyUser($dict['apiKeyUserId'], $dict['apiKeyId'], $dict['userId']);
    return $o;
  }

  /**
   * @param array $options
   * @param bool $single
   * @return ApiKeyUser|ApiKeyUser[]
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
      return Util::cast(parent::filter($options, $single), ApiKeyUser::class);
    }
    $objects = parent::filter($options, $single);
    if($join){
      return $objects;
    }
    $models = array();
    foreach($objects as $object){
      $models[] = Util::cast($object, ApiKeyUser::class);
    }
    return $models;
  }

  /**
   * @param string $pk
   * @return ApiKeyUser
   */
  function get($pk) {
    return Util::cast(parent::get($pk), ApiKeyUser::class);
  }

  /**
   * @param ApiKeyUser $model
   * @return ApiKeyUser
   */
  function save($model) {
    return Util::cast(parent::save($model), ApiKeyUser::class);
  }
}