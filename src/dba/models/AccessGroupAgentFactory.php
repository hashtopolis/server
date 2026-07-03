<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModelFactory;

/**
 * @extends AbstractModelFactory<AccessGroupAgent>
 */
class AccessGroupAgentFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "AccessGroupAgent";
  }
  
  function getModelTable(): string {
    return "AccessGroupAgent";
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
   * @return AccessGroupAgent
   */
  function getNullObject(): AccessGroupAgent {
    return new AccessGroupAgent(-1, null, null);
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return AccessGroupAgent
   */
  function createObjectFromDict($pk, $dict): AccessGroupAgent {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new AccessGroupAgent($dict['accessgroupagentid'], $dict['accessgroupid'], $dict['agentid']);
  }
}
