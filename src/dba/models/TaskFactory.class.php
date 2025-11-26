<?php

namespace DBA;

class TaskFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "Task";
  }
  
  function getModelTable(): string {
    return "Task";
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
   * @return Task
   */
  function getNullObject(): Task {
    return new Task(-1, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null);
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return Task
   */
  function createObjectFromDict($pk, $dict): Task {
    return new Task($dict['taskId'], $dict['taskName'], $dict['attackCmd'], $dict['chunkTime'], $dict['statusTimer'], $dict['keyspace'], $dict['keyspaceProgress'], $dict['priority'], $dict['maxAgents'], $dict['color'], $dict['isSmall'], $dict['isCpuTask'], $dict['useNewBench'], $dict['skipKeyspace'], $dict['crackerBinaryId'], $dict['crackerBinaryTypeId'], $dict['taskWrapperId'], $dict['isArchived'], $dict['notes'], $dict['staticChunks'], $dict['chunkSize'], $dict['forcePipe'], $dict['usePreprocessor'], $dict['preprocessorCommand']);
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return Task|Task[]
   */
  function filter(array $options, bool $single = false) {
    $join = false;
    if (array_key_exists('join', $options)) {
      $join = true;
    }
    if ($single) {
      if ($join) {
        return parent::filter($options, $single);
      }
      return Util::cast(parent::filter($options, $single), Task::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, Task::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return ?Task
   */
  function get($pk): ?Task {
    return Util::cast(parent::get($pk), Task::class);
  }
  
  /**
   * @param Task $model
   * @return Task
   */
  function save($model): Task {
    return Util::cast(parent::save($model), Task::class);
  }
}