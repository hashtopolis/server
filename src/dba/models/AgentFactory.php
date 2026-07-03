<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModelFactory;

/**
 * @extends AbstractModelFactory<Agent>
 */
class AgentFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "Agent";
  }
  
  function getModelTable(): string {
    return "Agent";
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
   * @return Agent
   */
  function getNullObject(): Agent {
    return new Agent(-1, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null);
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return Agent
   */
  function createObjectFromDict($pk, $dict): Agent {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new Agent($dict['agentid'], $dict['agentname'], $dict['uid'], $dict['os'], $dict['devices'], $dict['cmdpars'], $dict['ignoreerrors'], $dict['isactive'], $dict['istrusted'], $dict['token'], $dict['lastact'], $dict['lasttime'], $dict['lastip'], $dict['userid'], $dict['cpuonly'], $dict['clientsignature']);
  }
}
