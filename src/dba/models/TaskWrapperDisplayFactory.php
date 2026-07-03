<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModelFactory;

/**
 * @extends AbstractModelFactory<TaskWrapperDisplay>
 */
class TaskWrapperDisplayFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "TaskWrapperDisplay";
  }
  
  function getModelTable(): string {
    return "TaskWrapperDisplay";
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
   * @return TaskWrapperDisplay
   */
  function getNullObject(): TaskWrapperDisplay {
    return new TaskWrapperDisplay(-1, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null);
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return TaskWrapperDisplay
   */
  function createObjectFromDict($pk, $dict): TaskWrapperDisplay {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new TaskWrapperDisplay($dict['taskwrapperid'], $dict['taskwrapperpriority'], $dict['taskwrappermaxagents'], $dict['tasktype'], $dict['hashlistid'], $dict['accessgroupid'], $dict['taskwrappername'], $dict['displayname'], $dict['taskwrapperisarchived'], $dict['cracked'], $dict['taskid'], $dict['taskname'], $dict['color'], $dict['attackcmd'], $dict['chunktime'], $dict['statustimer'], $dict['keyspace'], $dict['keyspaceprogress'], $dict['taskpriority'], $dict['taskmaxagents'], $dict['issmall'], $dict['iscputask'], $dict['taskisarchived'], $dict['taskusepreprocessor'], $dict['hashlistname'], $dict['hashcount'], $dict['hashlistcracked'], $dict['hashtypeid'], $dict['hashtypedescription'], $dict['groupname']);
  }
}
