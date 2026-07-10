<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModelFactory;

/**
 * @extends AbstractModelFactory<AgentZap>
 */
class AgentZapFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "AgentZap";
  }
  
  function getModelTable(): string {
    return "AgentZap";
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
   * @return AgentZap
   */
  function getNullObject(): AgentZap {
    return new AgentZap(-1, null, null);
  }
  
  /**
   * @param array $dict
   * @return AgentZap
   */
  function createObjectFromDict(array $dict): AgentZap {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new AgentZap($dict['agentzapid'], $dict['agentid'], $dict['lastzapid']);
  }
}
