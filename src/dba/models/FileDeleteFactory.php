<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModelFactory;

/**
 * @extends AbstractModelFactory<FileDelete>
 */
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
   * @param array $dict
   * @return FileDelete
   */
  function createObjectFromDict(array $dict): FileDelete {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new FileDelete($dict['filedeleteid'], $dict['filename'], $dict['time']);
  }
}
