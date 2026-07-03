<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModelFactory;

/**
 * @extends AbstractModelFactory<CrackerBinary>
 */
class CrackerBinaryFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "CrackerBinary";
  }
  
  function getModelTable(): string {
    return "CrackerBinary";
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
   * @return CrackerBinary
   */
  function getNullObject(): CrackerBinary {
    return new CrackerBinary(-1, null, null, null, null);
  }
  
  /**
   * @param array $dict
   * @return CrackerBinary
   */
  function createObjectFromDict(array $dict): CrackerBinary {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new CrackerBinary($dict['crackerbinaryid'], $dict['crackerbinarytypeid'], $dict['version'], $dict['downloadurl'], $dict['binaryname']);
  }
}
