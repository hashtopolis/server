<?php

namespace DBA;

class FileFactory extends AbstractModelFactory {
  function getModelName() {
    return "File";
  }
  
  function getModelTable() {
    return "File";
  }
  
  function isCachable() {
    return false;
  }
  
  function getCacheValidTime() {
    return -1;
  }
  
  /**
   * @return File
   */
  function getNullObject() {
    $o = new File(-1, null, null, null, null, null, null);
    return $o;
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return File
   */
  function createObjectFromDict($pk, $dict) {
    $o = new File($dict['fileId'], $dict['filename'], $dict['size'], $dict['isSecret'], $dict['fileType'], $dict['accessGroupId'], $dict['lineCount']);
    return $o;
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
   * @return File
   */
  function get($pk) {
    return Util::cast(parent::get($pk), File::class);
  }
  
  /**
   * @param File $model
   * @return File
   */
  function save($model) {
    return Util::cast(parent::save($model), File::class);
  }
}