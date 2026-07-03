<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModelFactory;

/**
 * @extends AbstractModelFactory<FileTask>
 */
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
}
