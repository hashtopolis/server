<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModelFactory;

/**
 * @extends AbstractModelFactory<CrackerBinaryType>
 */
class CrackerBinaryTypeFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "CrackerBinaryType";
  }
  
  function getModelTable(): string {
    return "CrackerBinaryType";
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
   * @return CrackerBinaryType
   */
  function getNullObject(): CrackerBinaryType {
    return new CrackerBinaryType(-1, null, null);
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return CrackerBinaryType
   */
  function createObjectFromDict($pk, $dict): CrackerBinaryType {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new CrackerBinaryType($dict['crackerbinarytypeid'], $dict['typename'], $dict['ischunkingavailable']);
  }
}
