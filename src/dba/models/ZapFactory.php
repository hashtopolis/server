<?php

namespace Hashtopolis\dba\models;

use Exception;
use PDOStatement;
use Hashtopolis\dba\AbstractModelFactory;
use Hashtopolis\dba\AbstractModel;
use Hashtopolis\dba\Util;

class ZapFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "Zap";
  }
  
  function getModelTable(): string {
    return "Zap";
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
   * @return Zap
   */
  function getNullObject(): Zap {
    return new Zap(-1, null, null, null, null);
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return Zap
   */
  function createObjectFromDict($pk, $dict): Zap {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new Zap($dict['zapid'], $dict['hash'], $dict['solvetime'], $dict['agentid'], $dict['hashlistid']);
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return Zap|array|null
   * @throws Exception
   */
  function filter(array $options, bool $single = false): Zap|array|null {
    $join = false;
    if (array_key_exists('join', $options)) {
      $join = true;
    }
    if ($single) {
      if ($join) {
        return parent::filter($options, $single);
      }
      return Util::cast(parent::filter($options, $single), Zap::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, Zap::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return ?Zap
   * @throws Exception
   */
  function get($pk): ?Zap {
    return Util::cast(parent::get($pk), Zap::class);
  }

  /**
   * @param Zap $model
   * @param-out Zap $model
   * @param array $arr
   * @return PDOStatement
   * @throws Exception
   */
  function mset(AbstractModel &$model, array $arr): PDOStatement {
    assert($model instanceof Zap);
    $stmt = parent::mset($model, $arr);
    assert($model instanceof Zap);
    return $stmt;
  }

  /**
   * @param Zap $model
   * @param-out Zap $model
   * @param string $key key of the column to update
   * @param $value
   * @return PDOStatement
   * @throws Exception
   */
  function set(AbstractModel &$model, string $key, $value): PDOStatement {
    assert($model instanceof Zap);
    $stmt = parent::set($model, $key, $value);
    assert($model instanceof Zap);
    return $stmt;
  }
  
  /**
   * @param Zap $model
   * @return Zap
   * @throws Exception
   */
  function save($model): Zap {
    return Util::cast(parent::save($model), Zap::class);
  }
}
