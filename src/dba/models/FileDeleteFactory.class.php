<?php

namespace DBA;

class FileDeleteFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "FileDelete";
  }
  
  function getModelTable(): string {
    return "FileDelete";
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
    return new FileDelete($dict['fileDeleteId'], $dict['filename'], $dict['time']);
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return FileDelete|FileDelete[]
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
   */
  function get($pk): ?FileDelete {
    return Util::cast(parent::get($pk), FileDelete::class);
  }
  
  /**
   * @param FileDelete $model
   * @return FileDelete
   */
  function save($model): FileDelete {
    return Util::cast(parent::save($model), FileDelete::class);
  }
}