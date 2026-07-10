<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModelFactory;

/**
 * @extends AbstractModelFactory<Session>
 */
class SessionFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "Session";
  }
  
  function getModelTable(): string {
    return "Session";
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
   * @return Session
   */
  function getNullObject(): Session {
    return new Session(-1, null, null, null, null, null, null);
  }
  
  /**
   * @param array $dict
   * @return Session
   */
  function createObjectFromDict(array $dict): Session {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new Session($dict['sessionid'], $dict['userid'], $dict['sessionstartdate'], $dict['lastactiondate'], $dict['isopen'], $dict['sessionlifetime'], $dict['sessionkey']);
  }
}
