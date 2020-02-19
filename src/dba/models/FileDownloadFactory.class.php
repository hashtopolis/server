<?php

namespace DBA;

class FileDownloadFactory extends AbstractModelFactory {
  function getModelName() {
    return "FileDownload";
  }
  
  function getModelTable() {
    return "FileDownload";
  }
  
  function isCachable() {
    return false;
  }
  
  function getCacheValidTime() {
    return -1;
  }
  
  /**
   * @return FileDownload
   */
  function getNullObject() {
    $o = new FileDownload(-1, null, null, null);
    return $o;
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return FileDownload
   */
  function createObjectFromDict($pk, $dict) {
    $o = new FileDownload($dict['fileDownloadId'], $dict['time'], $dict['fileId'], $dict['status']);
    return $o;
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return FileDownload|FileDownload[]
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
      return Util::cast(parent::filter($options, $single), FileDownload::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, FileDownload::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return FileDownload
   */
  function get($pk) {
    return Util::cast(parent::get($pk), FileDownload::class);
  }
  
  /**
   * @param FileDownload $model
   * @return FileDownload
   */
  function save($model) {
    return Util::cast(parent::save($model), FileDownload::class);
  }
}