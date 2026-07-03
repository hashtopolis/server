<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModelFactory;

/**
 * @extends AbstractModelFactory<TaskWrapper>
 */
class TaskWrapperFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "TaskWrapper";
  }
  
  function getModelTable(): string {
    return "TaskWrapper";
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
   * @return TaskWrapper
   */
  function getNullObject(): TaskWrapper {
    return new TaskWrapper(-1, null, null, null, null, null, null, null, null);
  }
  
  /**
   * @param array $dict
   * @return TaskWrapper
   */
  function createObjectFromDict(array $dict): TaskWrapper {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new TaskWrapper($dict['taskwrapperid'], $dict['priority'], $dict['maxagents'], $dict['tasktype'], $dict['hashlistid'], $dict['accessgroupid'], $dict['taskwrappername'], $dict['isarchived'], $dict['cracked']);
  }
}
