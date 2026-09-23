<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModelFactory;

/**
 * @extends AbstractModelFactory<BackgroundJob>
 */
class BackgroundJobFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "BackgroundJob";
  }
  
  function getModelTable(): string {
    return "BackgroundJob";
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
   * @return BackgroundJob
   */
  function getNullObject(): BackgroundJob {
    return new BackgroundJob(-1, null, null, null, null, null, null, null, null, null);
  }
  
  /**
   * @param array $dict
   * @return BackgroundJob
   */
  function createObjectFromDict(array $dict): BackgroundJob {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new BackgroundJob($dict['backgroundjobid'], $dict['jobtype'], $dict['payload'], $dict['status'], $dict['userid'], $dict['createdat'], $dict['startedat'], $dict['finishedat'], $dict['exitcode'], $dict['resultmessage']);
  }
}
