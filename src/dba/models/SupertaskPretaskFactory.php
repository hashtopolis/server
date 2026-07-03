<?php

namespace Hashtopolis\dba\models;

use Exception;
use PDOStatement;
use Hashtopolis\dba\AbstractModelFactory;
use Hashtopolis\dba\AbstractModel;
use Hashtopolis\dba\Util;

class SupertaskPretaskFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "SupertaskPretask";
  }
  
  function getModelTable(): string {
    return "SupertaskPretask";
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
   * @return SupertaskPretask
   */
  function getNullObject(): SupertaskPretask {
    return new SupertaskPretask(-1, null, null);
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return SupertaskPretask
   */
  function createObjectFromDict($pk, $dict): SupertaskPretask {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new SupertaskPretask($dict['supertaskpretaskid'], $dict['supertaskid'], $dict['pretaskid']);
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return SupertaskPretask|array|null
   * @throws Exception
   */
  function filter(array $options, bool $single = false): SupertaskPretask|array|null {
    $join = false;
    if (array_key_exists('join', $options)) {
      $join = true;
    }
    if ($single) {
      if ($join) {
        return parent::filter($options, $single);
      }
      return Util::cast(parent::filter($options, $single), SupertaskPretask::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, SupertaskPretask::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return ?SupertaskPretask
   * @throws Exception
   */
  function get($pk): ?SupertaskPretask {
    return Util::cast(parent::get($pk), SupertaskPretask::class);
  }

  /**
   * @param ?SupertaskPretask $model
   * @param-out ?SupertaskPretask $model
   * @param array $arr
   * @return ?PDOStatement
   * @throws Exception
   */
  public function mset(?AbstractModel &$model, array $arr): ?PDOStatement {
    $stmt = parent::mset($model, $arr);
    assert($model instanceof SupertaskPretask);
    return $stmt;
  }

  /**
   * @param ?SupertaskPretask $model
   * @param-out ?SupertaskPretask $model
   * @param string $key key of the column to update
   * @param $value
   * @return ?PDOStatement
   * @throws Exception
   */
  public function set(?AbstractModel &$model, string $key, $value): ?PDOStatement {
    $stmt = parent::set($model, $key, $value);
    assert($model instanceof SupertaskPretask);
    return $stmt;
  }
  
  /**
   * @param SupertaskPretask $model
   * @return SupertaskPretask
   * @throws Exception
   */
  function save($model): SupertaskPretask {
    return Util::cast(parent::save($model), SupertaskPretask::class);
  }
}
