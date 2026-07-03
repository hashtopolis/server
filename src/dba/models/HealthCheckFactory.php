<?php

namespace Hashtopolis\dba\models;

use Exception;
use PDOStatement;
use Hashtopolis\dba\AbstractModelFactory;
use Hashtopolis\dba\AbstractModel;
use Hashtopolis\dba\Util;

class HealthCheckFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "HealthCheck";
  }
  
  function getModelTable(): string {
    return "HealthCheck";
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
   * @return HealthCheck
   */
  function getNullObject(): HealthCheck {
    return new HealthCheck(-1, null, null, null, null, null, null, null);
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return HealthCheck
   */
  function createObjectFromDict($pk, $dict): HealthCheck {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new HealthCheck($dict['healthcheckid'], $dict['time'], $dict['status'], $dict['checktype'], $dict['hashtypeid'], $dict['crackerbinaryid'], $dict['expectedcracks'], $dict['attackcmd']);
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return HealthCheck|array|null
   * @throws Exception
   */
  function filter(array $options, bool $single = false): HealthCheck|array|null {
    $join = false;
    if (array_key_exists('join', $options)) {
      $join = true;
    }
    if ($single) {
      if ($join) {
        return parent::filter($options, $single);
      }
      return Util::cast(parent::filter($options, $single), HealthCheck::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, HealthCheck::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return ?HealthCheck
   * @throws Exception
   */
  function get($pk): ?HealthCheck {
    return Util::cast(parent::get($pk), HealthCheck::class);
  }

  /**
   * @param HealthCheck $model
   * @param-out HealthCheck $model
   * @param array $arr
   * @return PDOStatement
   * @throws Exception
   */
  function mset(AbstractModel &$model, array $arr): PDOStatement {
    assert($model instanceof HealthCheck);
    $stmt = parent::mset($model, $arr);
    assert($model instanceof HealthCheck);
    return $stmt;
  }

  /**
   * @param HealthCheck $model
   * @param-out HealthCheck $model
   * @param string $key key of the column to update
   * @param $value
   * @return PDOStatement
   * @throws Exception
   */
  function set(AbstractModel &$model, string $key, $value): PDOStatement {
    assert($model instanceof HealthCheck);
    $stmt = parent::set($model, $key, $value);
    assert($model instanceof HealthCheck);
    return $stmt;
  }
  
  /**
   * @param HealthCheck $model
   * @return HealthCheck
   * @throws Exception
   */
  function save($model): HealthCheck {
    return Util::cast(parent::save($model), HealthCheck::class);
  }
}
