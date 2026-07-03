<?php

namespace Hashtopolis\dba\models;

use Exception;
use PDOStatement;
use Hashtopolis\dba\AbstractModelFactory;
use Hashtopolis\dba\AbstractModel;
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
   * @return Config|array|null
   * @throws Exception
   */
  function filter(array $options, bool $single = false): Config|array|null {
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
   * @throws Exception
   */
  function get($pk): ?Config {
    return Util::cast(parent::get($pk), Config::class);
  }

  /**
   * @param Config $model
   * @param-out Config $model
   * @param array $arr
   * @return PDOStatement
   * @throws Exception
   */
  function mset(AbstractModel &$model, array $arr): PDOStatement {
    assert($model instanceof Config);
    $stmt = parent::mset($model, $arr);
    assert($model instanceof Config);
    return $stmt;
  }

  /**
   * @param Config $model
   * @param-out Config $model
   * @param string $key key of the column to update
   * @param $value
   * @return PDOStatement
   * @throws Exception
   */
  function set(AbstractModel &$model, string $key, $value): PDOStatement {
    assert($model instanceof Config);
    $stmt = parent::set($model, $key, $value);
    assert($model instanceof Config);
    return $stmt;
  }
  
  /**
   * @param Config $model
   * @return Config
   * @throws Exception
   */
  function save($model): Config {
    return Util::cast(parent::save($model), Config::class);
  }
}
