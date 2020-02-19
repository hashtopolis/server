<?php

namespace DBA;

class LogEntryFactory extends AbstractModelFactory {
  function getModelName() {
    return "LogEntry";
  }
  
  function getModelTable() {
    return "LogEntry";
  }
  
  function isCachable() {
    return false;
  }
  
  function getCacheValidTime() {
    return -1;
  }
  
  /**
   * @return LogEntry
   */
  function getNullObject() {
    $o = new LogEntry(-1, null, null, null, null, null);
    return $o;
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return LogEntry
   */
  function createObjectFromDict($pk, $dict) {
    $o = new LogEntry($dict['logEntryId'], $dict['issuer'], $dict['issuerId'], $dict['level'], $dict['message'], $dict['time']);
    return $o;
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return LogEntry|LogEntry[]
   */
  function filter($options, $single = false) {
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
   * @return LogEntry
   */
  function get($pk) {
    return Util::cast(parent::get($pk), LogEntry::class);
  }
  
  /**
   * @param LogEntry $model
   * @return LogEntry
   */
  function save($model) {
    return Util::cast(parent::save($model), LogEntry::class);
  }
}