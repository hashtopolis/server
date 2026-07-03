<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModelFactory;

/**
 * @extends AbstractModelFactory<Task>
 */
class TaskFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "Task";
  }
  
  function getModelTable(): string {
    return "Task";
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
   * @return Task
   */
  function getNullObject(): Task {
    return new Task(-1, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null);
  }
  
  /**
   * @param array $dict
   * @return Task
   */
  function createObjectFromDict(array $dict): Task {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new Task($dict['taskid'], $dict['taskname'], $dict['attackcmd'], $dict['chunktime'], $dict['statustimer'], $dict['keyspace'], $dict['keyspaceprogress'], $dict['priority'], $dict['maxagents'], $dict['color'], $dict['issmall'], $dict['iscputask'], $dict['usenewbench'], $dict['skipkeyspace'], $dict['crackerbinaryid'], $dict['crackerbinarytypeid'], $dict['taskwrapperid'], $dict['isarchived'], $dict['notes'], $dict['staticchunks'], $dict['chunksize'], $dict['forcepipe'], $dict['usepreprocessor'], $dict['preprocessorcommand']);
  }
}
