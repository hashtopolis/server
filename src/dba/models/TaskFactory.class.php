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
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new Task($dict['taskid'], $dict['taskname'], $dict['attackcmd'], $dict['chunktime'], $dict['statustimer'], $dict['keyspace'], $dict['keyspaceprogress'], $dict['priority'], $dict['maxagents'], $dict['color'], $dict['issmall'], $dict['iscputask'], $dict['usenewbench'], $dict['skipkeyspace'], $dict['crackerbinaryid'], $dict['crackerbinarytypeid'], $dict['taskwrapperid'], $dict['isarchived'], $dict['notes'], $dict['staticchunks'], $dict['chunksize'], $dict['forcepipe'], $dict['usepreprocessor'], $dict['preprocessorcommand']);
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
