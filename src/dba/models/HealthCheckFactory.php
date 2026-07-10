<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModelFactory;

/**
 * @extends AbstractModelFactory<HealthCheck>
 */
class HealthCheckFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "HealthCheck";
  }
  
  function getModelTable(): string {
    return "HealthCheck";
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
   * @return HealthCheck
   */
  function getNullObject(): HealthCheck {
    return new HealthCheck(-1, null, null, null, null, null, null, null);
  }
  
  /**
   * @param array $dict
   * @return HealthCheck
   */
  function createObjectFromDict(array $dict): HealthCheck {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new HealthCheck($dict['healthcheckid'], $dict['time'], $dict['status'], $dict['checktype'], $dict['hashtypeid'], $dict['crackerbinaryid'], $dict['expectedcracks'], $dict['attackcmd']);
  }
}
