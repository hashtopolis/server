<?php

namespace Hashtopolis\dba\models;

use Exception;
use PDOStatement;
use Hashtopolis\dba\AbstractModelFactory;
use Hashtopolis\dba\AbstractModel;
use Hashtopolis\dba\Util;

class RightGroupFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "RightGroup";
  }
  
  function getModelTable(): string {
    return "RightGroup";
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
   * @return RightGroup
   */
  function getNullObject(): RightGroup {
    return new RightGroup(-1, null, null);
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return RightGroup
   */
  function createObjectFromDict($pk, $dict): RightGroup {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new RightGroup($dict['rightgroupid'], $dict['groupname'], $dict['permissions']);
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return RightGroup|array|null
   * @throws Exception
   */
  function filter(array $options, bool $single = false): RightGroup|array|null {
    $join = false;
    if (array_key_exists('join', $options)) {
      $join = true;
    }
    if ($single) {
      if ($join) {
        return parent::filter($options, $single);
      }
      return Util::cast(parent::filter($options, $single), RightGroup::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, RightGroup::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return ?RightGroup
   * @throws Exception
   */
  function get($pk): ?RightGroup {
    return Util::cast(parent::get($pk), RightGroup::class);
  }

  /**
   * @param RightGroup $model
   * @param array $arr
   * @return PDOStatement
   * @throws Exception
   */
  function mset(AbstractModel &$model, array $arr): PDOStatement {
    assert($model instanceof RightGroup);
    $stmt = parent::mset($model, $arr);
    assert($model instanceof RightGroup);
    return $stmt;
  }

  /**
   * @param RightGroup $model
   * @param $key string key of the column to update
   * @param $value
   * @return PDOStatement
   * @throws Exception
   */
  function set(AbstractModel &$model, string $key, $value): PDOStatement {
    assert($model instanceof RightGroup);
    $stmt = parent::set($model, $key, $value);
    assert($model instanceof RightGroup);
    return $stmt;
  }
  
  /**
   * @param RightGroup $model
   * @return RightGroup
   * @throws Exception
   */
  function save($model): RightGroup {
    return Util::cast(parent::save($model), RightGroup::class);
  }
}
