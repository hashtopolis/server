<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModelFactory;

/**
 * @extends AbstractModelFactory<FileDownload>
 */
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
   * @param array $dict
   * @return FileDownload
   */
  function createObjectFromDict(array $dict): FileDownload {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new FileDownload($dict['filedownloadid'], $dict['time'], $dict['fileid'], $dict['status']);
  }
}
