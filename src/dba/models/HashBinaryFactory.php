<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModelFactory;

/**
 * @extends AbstractModelFactory<HashBinary>
 */
class HashBinaryFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "HashBinary";
  }
  
  function getModelTable(): string {
    return "HashBinary";
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
   * @return HashBinary
   */
  function getNullObject(): HashBinary {
    return new HashBinary(-1, null, null, null, null, null, null, null, null);
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return HashBinary
   */
  function createObjectFromDict($pk, $dict): HashBinary {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new HashBinary($dict['hashbinaryid'], $dict['hashlistid'], $dict['essid'], $dict['hash'], $dict['plaintext'], $dict['timecracked'], $dict['chunkid'], $dict['iscracked'], $dict['crackpos']);
  }
}
