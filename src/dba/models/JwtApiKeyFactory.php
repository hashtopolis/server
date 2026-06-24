<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModelFactory;
use Hashtopolis\dba\Util;

class JwtApiKeyFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "JwtApiKey";
  }
  
  function getModelTable(): string {
    return "JwtApiKey";
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
   * @return JwtApiKey
   */
  function getNullObject(): JwtApiKey {
    return new JwtApiKey(-1, null, null, null, null);
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return JwtApiKey
   */
  function createObjectFromDict($pk, $dict): JwtApiKey {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new JwtApiKey($dict['jwtapikeyid'], $dict['startvalid'], $dict['endvalid'], $dict['userid'], $dict['isrevoked']);
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return JwtApiKey|JwtApiKey[]
   */
  function filter(array $options, bool $single = false): JwtApiKey|array|null {
    $join = false;
    if (array_key_exists('join', $options)) {
      $join = true;
    }
    if ($single) {
      if ($join) {
        return parent::filter($options, $single);
      }
      return Util::cast(parent::filter($options, $single), JwtApiKey::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, JwtApiKey::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return ?JwtApiKey
   */
  function get($pk): ?JwtApiKey {
    return Util::cast(parent::get($pk), JwtApiKey::class);
  }
  
  /**
   * @param JwtApiKey $model
   * @return JwtApiKey
   */
  function save($model): JwtApiKey {
    return Util::cast(parent::save($model), JwtApiKey::class);
  }
}
