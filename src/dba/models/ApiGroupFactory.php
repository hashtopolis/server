<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModelFactory;
use Hashtopolis\dba\Util;

class ApiGroupFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "ApiGroup";
  }
  
  function getModelTable(): string {
    return "ApiGroup";
  }

  function isMapping(): bool {
    return False;
  }
  
  function isCachable(): bool {
    return false;
  }
  
  function getCacheValidTime(): int {
    return -1;
  }
  
  /**
   * @return ApiGroup
   */
  function getNullObject(): ApiGroup {
    return new ApiGroup(-1, null, null);
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return ApiGroup
   */
  function createObjectFromDict($pk, $dict): ApiGroup {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new ApiGroup($dict['apigroupid'], $dict['permissions'], $dict['name']);
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return ApiGroup|ApiGroup[]
   */
  function filter(array $options, bool $single = false): ApiGroup|array {
    $join = false;
    if (array_key_exists('join', $options)) {
      $join = true;
    }
    if ($single) {
      if ($join) {
        return parent::filter($options, $single);
      }
      return Util::cast(parent::filter($options, $single), ApiGroup::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, ApiGroup::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return ?ApiGroup
   */
  function get($pk): ?ApiGroup {
    return Util::cast(parent::get($pk), ApiGroup::class);
  }
  
  /**
   * @param ApiGroup $model
   * @return ApiGroup
   */
  function save($model): ApiGroup {
    return Util::cast(parent::save($model), ApiGroup::class);
  }
}
