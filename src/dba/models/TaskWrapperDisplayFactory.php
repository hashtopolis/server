<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModelFactory;
use Hashtopolis\dba\Util;

class TaskWrapperDisplayFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "TaskWrapperDisplay";
  }
  
  function getModelTable(): string {
    return "TaskWrapperDisplay";
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
   * @return TaskWrapperDisplay
   */
  function getNullObject(): TaskWrapperDisplay {
    return new TaskWrapperDisplay(-1, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null);
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return TaskWrapperDisplay
   */
  function createObjectFromDict($pk, $dict): TaskWrapperDisplay {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new TaskWrapperDisplay($dict['taskwrapperid'], $dict['taskwrapperpriority'], $dict['taskwrappermaxagents'], $dict['tasktype'], $dict['hashlistid'], $dict['accessgroupid'], $dict['taskwrappername'], $dict['displayname'], $dict['taskwrapperisarchived'], $dict['cracked'], $dict['taskid'], $dict['taskname'], $dict['attackcmd'], $dict['chunktime'], $dict['statustimer'], $dict['keyspace'], $dict['keyspaceprogress'], $dict['taskpriority'], $dict['taskmaxagents'], $dict['issmall'], $dict['iscputask'], $dict['taskisarchived'], $dict['taskusepreprocessor']);
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return TaskWrapperDisplay|TaskWrapperDisplay[]
   */
  function filter(array $options, bool $single = false): TaskWrapperDisplay|array|null {
    $join = false;
    if (array_key_exists('join', $options)) {
      $join = true;
    }
    if ($single) {
      if ($join) {
        return parent::filter($options, $single);
      }
      return Util::cast(parent::filter($options, $single), TaskWrapperDisplay::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, TaskWrapperDisplay::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return ?TaskWrapperDisplay
   */
  function get($pk): ?TaskWrapperDisplay {
    return Util::cast(parent::get($pk), TaskWrapperDisplay::class);
  }
  
  /**
   * @param TaskWrapperDisplay $model
   * @return TaskWrapperDisplay
   */
  function save($model): TaskWrapperDisplay {
    return Util::cast(parent::save($model), TaskWrapperDisplay::class);
  }
}
