<?php

namespace Hashtopolis\dba\models;

use Exception;
use PDOStatement;
use Hashtopolis\dba\AbstractModelFactory;
use Hashtopolis\dba\AbstractModel;
use Hashtopolis\dba\Util;

class LogEntryFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "LogEntry";
  }
  
  function getModelTable(): string {
    return "LogEntry";
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
   * @return LogEntry
   */
  function getNullObject(): LogEntry {
    return new LogEntry(-1, null, null, null, null, null);
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return LogEntry
   */
  function createObjectFromDict($pk, $dict): LogEntry {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new LogEntry($dict['logentryid'], $dict['issuer'], $dict['issuerid'], $dict['level'], $dict['message'], $dict['time']);
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return LogEntry|array|null
   * @throws Exception
   */
  function filter(array $options, bool $single = false): LogEntry|array|null {
    $join = false;
    if (array_key_exists('join', $options)) {
      $join = true;
    }
    if ($single) {
      if ($join) {
        return parent::filter($options, $single);
      }
      return Util::cast(parent::filter($options, $single), LogEntry::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, LogEntry::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return ?LogEntry
   * @throws Exception
   */
  function get($pk): ?LogEntry {
    return Util::cast(parent::get($pk), LogEntry::class);
  }
  
  /**
   * @param LogEntry $model
   * @return ?LogEntry
   * @throws Exception
   */
  function save($model): ?LogEntry {
    return Util::cast(parent::save($model), LogEntry::class);
  }

  /**
   * @param LogEntry $model
   * @param array $arr key-value associations for update
   * @return LogEntry
   * @throws Exception
   */
  function mset($model, array $arr): LogEntry {
    return Util::cast(parent::mset($model, $arr), LogEntry::class);
  }

  /**
   * @param LogEntry $model
   * @param string $key key of the column to update
   * @param $value
   * @return LogEntry
   * @throws Exception
   */
  function set($model, string $key, $value): LogEntry {
    return Util::cast(parent::set($model, $key, $value), LogEntry::class);
  }
}
