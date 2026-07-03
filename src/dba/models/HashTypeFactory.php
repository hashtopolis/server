<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModelFactory;

/**
 * @extends AbstractModelFactory<HashType>
 */
class HashTypeFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "HashType";
  }
  
  function getModelTable(): string {
    return "HashType";
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
   * @return HashType
   */
  function getNullObject(): HashType {
    return new HashType(-1, null, null, null);
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return HashType
   */
  function createObjectFromDict($pk, $dict): HashType {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new HashType($dict['hashtypeid'], $dict['description'], $dict['issalted'], $dict['isslowhash']);
  }
}
