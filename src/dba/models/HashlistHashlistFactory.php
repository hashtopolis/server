<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModelFactory;

/**
 * @extends AbstractModelFactory<HashlistHashlist>
 */
class HashlistHashlistFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "HashlistHashlist";
  }
  
  function getModelTable(): string {
    return "HashlistHashlist";
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
   * @return HashlistHashlist
   */
  function getNullObject(): HashlistHashlist {
    return new HashlistHashlist(-1, null, null);
  }
  
  /**
   * @param array $dict
   * @return HashlistHashlist
   */
  function createObjectFromDict(array $dict): HashlistHashlist {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new HashlistHashlist($dict['hashlisthashlistid'], $dict['parenthashlistid'], $dict['hashlistid']);
  }
}
