<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModelFactory;

/**
 * @extends AbstractModelFactory<AgentError>
 */
class AgentErrorFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "AgentError";
  }
  
  function getModelTable(): string {
    return "AgentError";
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
   * @return AgentError
   */
  function getNullObject(): AgentError {
    return new AgentError(-1, null, null, null, null, null);
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return AgentError
   */
  function createObjectFromDict($pk, $dict): AgentError {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new AgentError($dict['agenterrorid'], $dict['agentid'], $dict['taskid'], $dict['chunkid'], $dict['time'], $dict['error']);
  }
}
