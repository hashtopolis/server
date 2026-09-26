<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModelFactory;

/**
 * @extends AbstractModelFactory<BrokenTask>
 */
class BrokenTaskFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "BrokenTask";
  }
  
  function getModelTable(): string {
    return "BrokenTask";
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
   * @return BrokenTask
   */
  function getNullObject(): BrokenTask {
    return new BrokenTask(-1, null, null, null);
  }
  
  /**
   * @param array $dict
   * @return BrokenTask
   */
  function createObjectFromDict(array $dict): BrokenTask {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new BrokenTask($dict['brokentaskid'], $dict['taskid'], $dict['time'], $dict['reason']);
  }
}
