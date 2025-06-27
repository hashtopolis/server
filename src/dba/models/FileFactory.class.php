<?php

namespace DBA;

class FileFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "File";
  }
  
  function getModelTable(): string {
    return "File";
  }
  
  function isCachable(): bool {
    return false;
  }
  
  function getCacheValidTime(): int {
    return -1;
  }
  
  /**
   * @return File
   */
  function getNullObject(): File {
    return new File(-1, null, null, null, null, null, null);
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return File
   */
  function createObjectFromDict($pk, $dict): File {
    return new File($dict['fileId'], $dict['filename'], $dict['size'], $dict['isSecret'], $dict['fileType'], $dict['accessGroupId'], $dict['lineCount']);
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return File|File[]
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
      return Util::cast(parent::filter($options, $single), File::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, File::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return ?File
   */
  function get($pk): ?File {
    return Util::cast(parent::get($pk), File::class);
  }
  
  /**
   * @param File $model
   * @return File
   */
  function save($model): File {
    return Util::cast(parent::save($model), File::class);
  }
}