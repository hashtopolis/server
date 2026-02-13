<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModelFactory;
use Hashtopolis\dba\Util;

class ConfigFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "Config";
  }
  
  function getModelTable(): string {
    return "Config";
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
   * @return Config
   */
  function getNullObject(): Config {
    return new Config(-1, null, null, null);
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return Config
   */
  function createObjectFromDict($pk, $dict): Config {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new Config($dict['configid'], $dict['configsectionid'], $dict['item'], $dict['value']);
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return Config|Config[]
   */
  function filter(array $options, bool $single = false): Config|array {
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
   * @return ?Config
   */
  function get($pk): ?Config {
    return Util::cast(parent::get($pk), Config::class);
  }
  
  /**
   * @param Config $model
   * @return Config
   */
  function save($model): Config {
    return Util::cast(parent::save($model), Config::class);
  }
}
