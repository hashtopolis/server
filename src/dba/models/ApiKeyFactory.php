<?php

namespace Hashtopolis\dba\models;

use Exception;
use PDOStatement;
use Hashtopolis\dba\AbstractModelFactory;
use Hashtopolis\dba\AbstractModel;
use Hashtopolis\dba\Util;

class ApiKeyFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "ApiKey";
  }
  
  function getModelTable(): string {
    return "ApiKey";
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
   * @return ApiKey
   */
  function getNullObject(): ApiKey {
    return new ApiKey(-1, null, null, null, null, null, null);
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return ApiKey
   */
  function createObjectFromDict($pk, $dict): ApiKey {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new ApiKey($dict['apikeyid'], $dict['startvalid'], $dict['endvalid'], $dict['accesskey'], $dict['accesscount'], $dict['userid'], $dict['apigroupid']);
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return ApiKey|array|null
   * @throws Exception
   */
  function filter(array $options, bool $single = false): ApiKey|array|null {
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
   * @return ?ApiKey
   * @throws Exception
   */
  function get($pk): ?ApiKey {
    return Util::cast(parent::get($pk), ApiKey::class);
  }
  
  /**
   * @param ApiKey $model
   * @return ApiKey
   * @throws Exception
   */
  function save($model): ApiKey {
    return Util::cast(parent::save($model), ApiKey::class);
  }

  /**
   * @param ApiKey $model
   * @param array $arr key-value associations for update
   * @return ApiKey
   * @throws Exception
   */
  function mset($model, array $arr): ApiKey {
    return Util::cast(parent::mset($model, $arr), ApiKey::class);
  }
}
