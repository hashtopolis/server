<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModelFactory;

/**
 * @extends AbstractModelFactory<Assignment>
 */
class AssignmentFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "Assignment";
  }
  
  function getModelTable(): string {
    return "Assignment";
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
   * @return Assignment
   */
  function getNullObject(): Assignment {
    return new Assignment(-1, null, null, null);
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return Assignment
   */
  function createObjectFromDict($pk, $dict): Assignment {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new Assignment($dict['assignmentid'], $dict['taskid'], $dict['agentid'], $dict['benchmark']);
  }
}
