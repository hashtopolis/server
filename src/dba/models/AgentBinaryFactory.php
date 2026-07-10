<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModelFactory;

/**
 * @extends AbstractModelFactory<AgentBinary>
 */
class AgentBinaryFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "AgentBinary";
  }
  
  function getModelTable(): string {
    return "AgentBinary";
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
   * @return AgentBinary
   */
  function getNullObject(): AgentBinary {
    return new AgentBinary(-1, null, null, null, null, null, null);
  }
  
  /**
   * @param array $dict
   * @return AgentBinary
   */
  function createObjectFromDict(array $dict): AgentBinary {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new AgentBinary($dict['agentbinaryid'], $dict['binarytype'], $dict['version'], $dict['operatingsystems'], $dict['filename'], $dict['updatetrack'], $dict['updateavailable']);
  }
}
