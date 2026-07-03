<?php

namespace Hashtopolis\dba\models;

use Exception;
use PDOStatement;
use Hashtopolis\dba\AbstractModelFactory;
use Hashtopolis\dba\AbstractModel;
use Hashtopolis\dba\Util;

class TaskWrapperFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "TaskWrapper";
  }
  
  function getModelTable(): string {
    return "TaskWrapper";
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
   * @return TaskWrapper
   */
  function getNullObject(): TaskWrapper {
    return new TaskWrapper(-1, null, null, null, null, null, null, null, null);
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return TaskWrapper
   */
  function createObjectFromDict($pk, $dict): TaskWrapper {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new TaskWrapper($dict['taskwrapperid'], $dict['priority'], $dict['maxagents'], $dict['tasktype'], $dict['hashlistid'], $dict['accessgroupid'], $dict['taskwrappername'], $dict['isarchived'], $dict['cracked']);
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return TaskWrapper|array|null
   * @throws Exception
   */
  function filter(array $options, bool $single = false): TaskWrapper|array|null {
    $join = false;
    if (array_key_exists('join', $options)) {
      $join = true;
    }
    if ($single) {
      if ($join) {
        return parent::filter($options, $single);
      }
      return Util::cast(parent::filter($options, $single), TaskWrapper::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, TaskWrapper::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return ?TaskWrapper
   * @throws Exception
   */
  function get($pk): ?TaskWrapper {
    return Util::cast(parent::get($pk), TaskWrapper::class);
  }

  /**
   * @param TaskWrapper $model
   * @param-out TaskWrapper $model
   * @param array $arr
   * @return PDOStatement
   * @throws Exception
   */
  function mset(AbstractModel &$model, array $arr): PDOStatement {
    assert($model instanceof TaskWrapper);
    $stmt = parent::mset($model, $arr);
    assert($model instanceof TaskWrapper);
    return $stmt;
  }

  /**
   * @param TaskWrapper $model
   * @param-out TaskWrapper $model
   * @param string $key key of the column to update
   * @param $value
   * @return PDOStatement
   * @throws Exception
   */
  function set(AbstractModel &$model, string $key, $value): PDOStatement {
    assert($model instanceof TaskWrapper);
    $stmt = parent::set($model, $key, $value);
    assert($model instanceof TaskWrapper);
    return $stmt;
  }
  
  /**
   * @param TaskWrapper $model
   * @return TaskWrapper
   * @throws Exception
   */
  function save($model): TaskWrapper {
    return Util::cast(parent::save($model), TaskWrapper::class);
  }
}
