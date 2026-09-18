<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModelFactory;

/**
 * @extends AbstractModelFactory<CrackerBinaryHashtype>
 */
class CrackerBinaryHashtypeFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "CrackerBinaryHashtype";
  }
  
  function getModelTable(): string {
    return "CrackerBinaryHashtype";
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
   * @return CrackerBinaryHashtype
   */
  function getNullObject(): CrackerBinaryHashtype {
    return new CrackerBinaryHashtype(-1, null, null);
  }
  
  /**
   * @param array $dict
   * @return CrackerBinaryHashtype
   */
  function createObjectFromDict(array $dict): CrackerBinaryHashtype {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new CrackerBinaryHashtype($dict['crackerbinaryhashtypeid'], $dict['crackerbinaryid'], $dict['hashtypeid']);
  }
}
