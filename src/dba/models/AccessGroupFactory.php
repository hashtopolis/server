<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModelFactory;

/**
 * @extends AbstractModelFactory<AccessGroup>
 */
class AccessGroupFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "AccessGroup";
  }
  
  function getModelTable(): string {
    return "AccessGroup";
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
   * @return AccessGroup
   */
  function getNullObject(): AccessGroup {
    return new AccessGroup(-1, null);
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return AccessGroup
   */
  function createObjectFromDict($pk, $dict): AccessGroup {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new AccessGroup($dict['accessgroupid'], $dict['groupname']);
  }
}
