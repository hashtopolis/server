<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModelFactory;

/**
 * @extends AbstractModelFactory<Speed>
 */
class SpeedFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "Speed";
  }
  
  function getModelTable(): string {
    return "Speed";
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
   * @return Speed
   */
  function getNullObject(): Speed {
    return new Speed(-1, null, null, null, null);
  }
  
  /**
   * @param array $dict
   * @return Speed
   */
  function createObjectFromDict(array $dict): Speed {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new Speed($dict['speedid'], $dict['agentid'], $dict['taskid'], $dict['speed'], $dict['time']);
  }
}
