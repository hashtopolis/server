<?php

namespace Hashtopolis\dba\models;

use Exception;
use PDOStatement;
use Hashtopolis\dba\AbstractModelFactory;
use Hashtopolis\dba\AbstractModel;
use Hashtopolis\dba\Util;

class AccessGroupFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "AccessGroup";
  }
  
  function getModelTable(): string {
    return "AccessGroup";
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
   * @return AccessGroup
   */
  function getNullObject(): AccessGroup {
    return new AccessGroup(-1, null);
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return AccessGroup
   */
  function createObjectFromDict($pk, $dict): AccessGroup {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new AccessGroup($dict['accessgroupid'], $dict['groupname']);
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return AccessGroup|array|null
   * @throws Exception
   */
  function filter(array $options, bool $single = false): AccessGroup|array|null {
    $join = false;
    if (array_key_exists('join', $options)) {
      $join = true;
    }
    if ($single) {
      if ($join) {
        return parent::filter($options, $single);
      }
      return Util::cast(parent::filter($options, $single), AccessGroup::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, AccessGroup::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return ?AccessGroup
   * @throws Exception
   */
  function get($pk): ?AccessGroup {
    return Util::cast(parent::get($pk), AccessGroup::class);
  }
  
  /**
   * @param AccessGroup $model
   * @return ?AccessGroup
   * @throws Exception
   */
  function save($model): ?AccessGroup {
    return Util::cast(parent::save($model), AccessGroup::class);
  }

  /**
   * @param AccessGroup $model
   * @param array $arr key-value associations for update
   * @return AccessGroup
   * @throws Exception
   */
  function mset($model, array $arr): AccessGroup {
    return Util::cast(parent::mset($model, $arr), AccessGroup::class);
  }

  /**
   * @param AccessGroup $model
   * @param string $key key of the column to update
   * @param $value
   * @return AccessGroup
   * @throws Exception
   */
  function set($model, string $key, $value): AccessGroup {
    return Util::cast(parent::set($model, $key, $value), AccessGroup::class);
  }
}
