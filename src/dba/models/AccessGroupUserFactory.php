<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModelFactory;

/**
 * @extends AbstractModelFactory<AccessGroupUser>
 */
class AccessGroupUserFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "AccessGroupUser";
  }
  
  function getModelTable(): string {
    return "AccessGroupUser";
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
   * @return AccessGroupUser
   */
  function getNullObject(): AccessGroupUser {
    return new AccessGroupUser(-1, null, null);
  }
  
  /**
   * @param array $dict
   * @return AccessGroupUser
   */
  function createObjectFromDict(array $dict): AccessGroupUser {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new AccessGroupUser($dict['accessgroupuserid'], $dict['accessgroupid'], $dict['userid']);
  }
}
