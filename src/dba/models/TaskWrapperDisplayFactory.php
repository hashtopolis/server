<?php

namespace Hashtopolis\dba\models;

use Exception;
use PDOStatement;
use Hashtopolis\dba\AbstractModelFactory;
use Hashtopolis\dba\AbstractModel;
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
    return new TaskWrapperDisplay(-1, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null);
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
    return new TaskWrapperDisplay($dict['taskwrapperid'], $dict['taskwrapperpriority'], $dict['taskwrappermaxagents'], $dict['tasktype'], $dict['hashlistid'], $dict['accessgroupid'], $dict['taskwrappername'], $dict['displayname'], $dict['taskwrapperisarchived'], $dict['cracked'], $dict['taskid'], $dict['taskname'], $dict['color'], $dict['attackcmd'], $dict['chunktime'], $dict['statustimer'], $dict['keyspace'], $dict['keyspaceprogress'], $dict['taskpriority'], $dict['taskmaxagents'], $dict['issmall'], $dict['iscputask'], $dict['taskisarchived'], $dict['taskusepreprocessor'], $dict['hashlistname'], $dict['hashcount'], $dict['hashlistcracked'], $dict['hashtypeid'], $dict['hashtypedescription'], $dict['groupname']);
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return TaskWrapperDisplay|array|null
   * @throws Exception
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
   * @throws Exception
   */
  function get($pk): ?TaskWrapperDisplay {
    return Util::cast(parent::get($pk), TaskWrapperDisplay::class);
  }
  
  /**
   * @param TaskWrapperDisplay $model
   * @return ?TaskWrapperDisplay
   * @throws Exception
   */
  function save($model): ?TaskWrapperDisplay {
    return Util::cast(parent::save($model), TaskWrapperDisplay::class);
  }

  /**
   * @param TaskWrapperDisplay $model
   * @param array $arr key-value associations for update
   * @return TaskWrapperDisplay
   * @throws Exception
   */
  function mset($model, array $arr): TaskWrapperDisplay {
    return Util::cast(parent::mset($model, $arr), TaskWrapperDisplay::class);
  }

  /**
   * @param TaskWrapperDisplay $model
   * @param string $key key of the column to update
   * @param $value
   * @return TaskWrapperDisplay
   * @throws Exception
   */
  function set($model, string $key, $value): TaskWrapperDisplay {
    return Util::cast(parent::set($model, $key, $value), TaskWrapperDisplay::class);
  }
}
