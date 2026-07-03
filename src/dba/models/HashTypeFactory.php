<?php

namespace Hashtopolis\dba\models;

use Exception;
use PDOStatement;
use Hashtopolis\dba\AbstractModelFactory;
use Hashtopolis\dba\AbstractModel;
use Hashtopolis\dba\Util;

class HashTypeFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "HashType";
  }
  
  function getModelTable(): string {
    return "HashType";
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
   * @return HashType
   */
  function getNullObject(): HashType {
    return new HashType(-1, null, null, null);
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return HashType
   */
  function createObjectFromDict($pk, $dict): HashType {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new HashType($dict['hashtypeid'], $dict['description'], $dict['issalted'], $dict['isslowhash']);
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return HashType|array|null
   * @throws Exception
   */
  function filter(array $options, bool $single = false): HashType|array|null {
    $join = false;
    if (array_key_exists('join', $options)) {
      $join = true;
    }
    if ($single) {
      if ($join) {
        return parent::filter($options, $single);
      }
      return Util::cast(parent::filter($options, $single), HashType::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, HashType::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return ?HashType
   * @throws Exception
   */
  function get($pk): ?HashType {
    return Util::cast(parent::get($pk), HashType::class);
  }
  
  /**
   * @param HashType $model
   * @return ?HashType
   * @throws Exception
   */
  function save($model): ?HashType {
    return Util::cast(parent::save($model), HashType::class);
  }

  /**
   * @param HashType $model
   * @param array $arr key-value associations for update
   * @return HashType
   * @throws Exception
   */
  function mset($model, array $arr): HashType {
    return Util::cast(parent::mset($model, $arr), HashType::class);
  }

  /**
   * @param HashType $model
   * @param string $key key of the column to update
   * @param $value
   * @return HashType
   * @throws Exception
   */
  function set($model, string $key, $value): HashType {
    return Util::cast(parent::set($model, $key, $value), HashType::class);
  }
}
