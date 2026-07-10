<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModelFactory;

/**
 * @extends AbstractModelFactory<RightGroup>
 */
class RightGroupFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "RightGroup";
  }
  
  function getModelTable(): string {
    return "RightGroup";
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
   * @return RightGroup
   */
  function getNullObject(): RightGroup {
    return new RightGroup(-1, null, null);
  }
  
  /**
   * @param array $dict
   * @return RightGroup
   */
  function createObjectFromDict(array $dict): RightGroup {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new RightGroup($dict['rightgroupid'], $dict['groupname'], $dict['permissions']);
  }
}
