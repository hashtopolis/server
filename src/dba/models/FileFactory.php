<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModelFactory;

/**
 * @extends AbstractModelFactory<File>
 */
class FileFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "File";
  }
  
  function getModelTable(): string {
    return "File";
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
   * @return File
   */
  function getNullObject(): File {
    return new File(-1, null, null, null, null, null, null);
  }
  
  /**
   * @param array $dict
   * @return File
   */
  function createObjectFromDict(array $dict): File {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new File($dict['fileid'], $dict['filename'], $dict['size'], $dict['issecret'], $dict['filetype'], $dict['accessgroupid'], $dict['linecount']);
  }
}
