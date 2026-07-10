<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModelFactory;

/**
 * @extends AbstractModelFactory<Hash>
 */
class HashFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "Hash";
  }
  
  function getModelTable(): string {
    return "Hash";
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
   * @return Hash
   */
  function getNullObject(): Hash {
    return new Hash(-1, null, null, null, null, null, null, null, null);
  }
  
  /**
   * @param array $dict
   * @return Hash
   */
  function createObjectFromDict(array $dict): Hash {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new Hash($dict['hashid'], $dict['hashlistid'], $dict['hash'], $dict['salt'], $dict['plaintext'], $dict['timecracked'], $dict['chunkid'], $dict['iscracked'], $dict['crackpos']);
  }
}
