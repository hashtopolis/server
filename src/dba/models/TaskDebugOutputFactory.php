<?php

namespace Hashtopolis\dba\models;

use Exception;
use PDOStatement;
use Hashtopolis\dba\AbstractModelFactory;
use Hashtopolis\dba\AbstractModel;
use Hashtopolis\dba\Util;

class TaskDebugOutputFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "TaskDebugOutput";
  }
  
  function getModelTable(): string {
    return "TaskDebugOutput";
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
   * @return TaskDebugOutput
   */
  function getNullObject(): TaskDebugOutput {
    return new TaskDebugOutput(-1, null, null);
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return TaskDebugOutput
   */
  function createObjectFromDict($pk, $dict): TaskDebugOutput {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new TaskDebugOutput($dict['taskdebugoutputid'], $dict['taskid'], $dict['output']);
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return TaskDebugOutput|array|null
   * @throws Exception
   */
  function filter(array $options, bool $single = false): TaskDebugOutput|array|null {
    $join = false;
    if (array_key_exists('join', $options)) {
      $join = true;
    }
    if ($single) {
      if ($join) {
        return parent::filter($options, $single);
      }
      return Util::cast(parent::filter($options, $single), TaskDebugOutput::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, TaskDebugOutput::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return ?TaskDebugOutput
   * @throws Exception
   */
  function get($pk): ?TaskDebugOutput {
    return Util::cast(parent::get($pk), TaskDebugOutput::class);
  }
  
  /**
   * @param TaskDebugOutput $model
   * @return ?TaskDebugOutput
   * @throws Exception
   */
  function save($model): ?TaskDebugOutput {
    return Util::cast(parent::save($model), TaskDebugOutput::class);
  }

  /**
   * @param TaskDebugOutput $model
   * @param array $arr key-value associations for update
   * @return TaskDebugOutput
   * @throws Exception
   */
  function mset($model, array $arr): TaskDebugOutput {
    return Util::cast(parent::mset($model, $arr), TaskDebugOutput::class);
  }

  /**
   * @param TaskDebugOutput $model
   * @param string $key key of the column to update
   * @param $value
   * @return TaskDebugOutput
   * @throws Exception
   */
  function set($model, string $key, $value): TaskDebugOutput {
    return Util::cast(parent::set($model, $key, $value), TaskDebugOutput::class);
  }
}
