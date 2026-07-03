<?php

namespace Hashtopolis\dba\models;

use Exception;
use PDOStatement;
use Hashtopolis\dba\AbstractModelFactory;
use Hashtopolis\dba\AbstractModel;
use Hashtopolis\dba\Util;

class FileTaskFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "FileTask";
  }
  
  function getModelTable(): string {
    return "FileTask";
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
   * @return FileTask
   */
  function getNullObject(): FileTask {
    return new FileTask(-1, null, null);
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return FileTask
   */
  function createObjectFromDict($pk, $dict): FileTask {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new FileTask($dict['filetaskid'], $dict['fileid'], $dict['taskid']);
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return FileTask|array|null
   * @throws Exception
   */
  function filter(array $options, bool $single = false): FileTask|array|null {
    $join = false;
    if (array_key_exists('join', $options)) {
      $join = true;
    }
    if ($single) {
      if ($join) {
        return parent::filter($options, $single);
      }
      return Util::cast(parent::filter($options, $single), FileTask::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, FileTask::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return ?FileTask
   * @throws Exception
   */
  function get($pk): ?FileTask {
    return Util::cast(parent::get($pk), FileTask::class);
  }
  
  /**
   * @param FileTask $model
   * @return FileTask
   * @throws Exception
   */
  function save($model): FileTask {
    return Util::cast(parent::save($model), FileTask::class);
  }

  /**
   * @param FileTask $model
   * @param array $arr key-value associations for update
   * @return FileTask
   * @throws Exception
   */
  function mset($model, array $arr): FileTask {
    return Util::cast(parent::mset($model, $arr), FileTask::class);
  }

  /**
   * @param FileTask $model
   * @param string $key key of the column to update
   * @param $value
   * @return FileTask
   * @throws Exception
   */
  function set($model, string $key, $value): FileTask {
    return Util::cast(parent::set($model, $key, $value), FileTask::class);
  }
}
