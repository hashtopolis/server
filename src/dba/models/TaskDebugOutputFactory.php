<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModelFactory;

/**
 * @extends AbstractModelFactory<TaskDebugOutput>
 */
class TaskDebugOutputFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "TaskDebugOutput";
  }
  
  function getModelTable(): string {
    return "TaskDebugOutput";
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
   * @return TaskDebugOutput
   */
  function getNullObject(): TaskDebugOutput {
    return new TaskDebugOutput(-1, null, null);
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return TaskDebugOutput
   */
  function createObjectFromDict($pk, $dict): TaskDebugOutput {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new TaskDebugOutput($dict['taskdebugoutputid'], $dict['taskid'], $dict['output']);
  }
}
