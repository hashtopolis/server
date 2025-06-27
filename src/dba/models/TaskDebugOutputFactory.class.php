<?php

namespace DBA;

class TaskDebugOutputFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "TaskDebugOutput";
  }
  
  function getModelTable(): string {
    return "TaskDebugOutput";
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
    return new TaskDebugOutput($dict['taskDebugOutputId'], $dict['taskId'], $dict['output']);
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return TaskDebugOutput|TaskDebugOutput[]
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
   */
  function get($pk): ?TaskDebugOutput {
    return Util::cast(parent::get($pk), TaskDebugOutput::class);
  }
  
  /**
   * @param TaskDebugOutput $model
   * @return TaskDebugOutput
   */
  function save($model): TaskDebugOutput {
    return Util::cast(parent::save($model), TaskDebugOutput::class);
  }
}