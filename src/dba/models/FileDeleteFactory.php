<?php

namespace Hashtopolis\dba\models;

use Exception;
use PDOStatement;
use Hashtopolis\dba\AbstractModelFactory;
use Hashtopolis\dba\AbstractModel;
use Hashtopolis\dba\Util;

class FileDeleteFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "FileDelete";
  }
  
  function getModelTable(): string {
    return "FileDelete";
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
   * @return FileDelete
   */
  function getNullObject(): FileDelete {
    return new FileDelete(-1, null, null);
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return FileDelete
   */
  function createObjectFromDict($pk, $dict): FileDelete {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new FileDelete($dict['filedeleteid'], $dict['filename'], $dict['time']);
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return FileDelete|array|null
   * @throws Exception
   */
  function filter(array $options, bool $single = false): FileDelete|array|null {
    $join = false;
    if (array_key_exists('join', $options)) {
      $join = true;
    }
    if ($single) {
      if ($join) {
        return parent::filter($options, $single);
      }
      return Util::cast(parent::filter($options, $single), FileDelete::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, FileDelete::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return ?FileDelete
   * @throws Exception
   */
  function get($pk): ?FileDelete {
    return Util::cast(parent::get($pk), FileDelete::class);
  }
  
  /**
   * @param FileDelete $model
   * @return FileDelete
   * @throws Exception
   */
  function save($model): FileDelete {
    return Util::cast(parent::save($model), FileDelete::class);
  }

  /**
   * @param FileDelete $model
   * @param array $arr key-value associations for update
   * @return FileDelete
   * @throws Exception
   */
  function mset($model, array $arr): FileDelete {
    return Util::cast(parent::mset($model, $arr), FileDelete::class);
  }
}
