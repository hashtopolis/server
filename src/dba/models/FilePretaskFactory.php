<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModelFactory;

/**
 * @extends AbstractModelFactory<FilePretask>
 */
class FilePretaskFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "FilePretask";
  }
  
  function getModelTable(): string {
    return "FilePretask";
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
   * @return FilePretask
   */
  function getNullObject(): FilePretask {
    return new FilePretask(-1, null, null);
  }
  
  /**
   * @param array $dict
   * @return FilePretask
   */
  function createObjectFromDict(array $dict): FilePretask {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new FilePretask($dict['filepretaskid'], $dict['fileid'], $dict['pretaskid']);
  }
}
