<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModelFactory;

/**
 * @extends AbstractModelFactory<AgentStat>
 */
class AgentStatFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "AgentStat";
  }
  
  function getModelTable(): string {
    return "AgentStat";
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
   * @return AgentStat
   */
  function getNullObject(): AgentStat {
    return new AgentStat(-1, null, null, null, null);
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return AgentStat
   */
  function createObjectFromDict($pk, $dict): AgentStat {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new AgentStat($dict['agentstatid'], $dict['agentid'], $dict['stattype'], $dict['time'], $dict['value']);
  }
}
