<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModelFactory;

/**
 * @extends AbstractModelFactory<StoredValue>
 */
class StoredValueFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "StoredValue";
  }
  
  function getModelTable(): string {
    return "StoredValue";
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
   * @return StoredValue
   */
  function getNullObject(): StoredValue {
    return new StoredValue(-1, null);
  }
  
  /**
   * @param array $dict
   * @return StoredValue
   */
  function createObjectFromDict(array $dict): StoredValue {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new StoredValue($dict['storedvalueid'], $dict['val']);
  }
}
