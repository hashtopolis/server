<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModelFactory;

/**
 * @extends AbstractModelFactory<Zap>
 */
class ZapFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "Zap";
  }
  
  function getModelTable(): string {
    return "Zap";
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
   * @return Zap
   */
  function getNullObject(): Zap {
    return new Zap(-1, null, null, null, null);
  }
  
  /**
   * @param array $dict
   * @return Zap
   */
  function createObjectFromDict(array $dict): Zap {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new Zap($dict['zapid'], $dict['hash'], $dict['solvetime'], $dict['agentid'], $dict['hashlistid']);
  }
}
