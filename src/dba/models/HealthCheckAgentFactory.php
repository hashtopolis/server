<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModelFactory;

/**
 * @extends AbstractModelFactory<HealthCheckAgent>
 */
class HealthCheckAgentFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "HealthCheckAgent";
  }
  
  function getModelTable(): string {
    return "HealthCheckAgent";
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
   * @return HealthCheckAgent
   */
  function getNullObject(): HealthCheckAgent {
    return new HealthCheckAgent(-1, null, null, null, null, null, null, null, null);
  }
  
  /**
   * @param array $dict
   * @return HealthCheckAgent
   */
  function createObjectFromDict(array $dict): HealthCheckAgent {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    $dict['end'] = $dict['htp_end'];
    return new HealthCheckAgent($dict['healthcheckagentid'], $dict['healthcheckid'], $dict['agentid'], $dict['status'], $dict['cracked'], $dict['numgpus'], $dict['start'], $dict['end'], $dict['errors']);
  }
}
