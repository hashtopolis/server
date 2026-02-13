<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModelFactory;
use Hashtopolis\dba\Util;

class FileDownloadFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "FileDownload";
  }
  
  function getModelTable(): string {
    return "FileDownload";
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
   * @return FileDownload
   */
  function getNullObject(): FileDownload {
    return new FileDownload(-1, null, null, null);
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return FileDownload
   */
  function createObjectFromDict($pk, $dict): FileDownload {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new FileDownload($dict['filedownloadid'], $dict['time'], $dict['fileid'], $dict['status']);
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return FileDownload|FileDownload[]
   */
  function filter(array $options, bool $single = false): FileDownload|array {
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
   * @return ?FileDownload
   */
  function get($pk): ?FileDownload {
    return Util::cast(parent::get($pk), FileDownload::class);
  }
  
  /**
   * @param FileDownload $model
   * @return FileDownload
   */
  function save($model): FileDownload {
    return Util::cast(parent::save($model), FileDownload::class);
  }
}
