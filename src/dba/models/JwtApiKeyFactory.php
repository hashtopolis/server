<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModelFactory;

/**
 * @extends AbstractModelFactory<JwtApiKey>
 */
class JwtApiKeyFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "JwtApiKey";
  }
  
  function getModelTable(): string {
    return "JwtApiKey";
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
   * @return JwtApiKey
   */
  function getNullObject(): JwtApiKey {
    return new JwtApiKey(-1, null, null, null, null);
  }
  
  /**
   * @param array $dict
   * @return JwtApiKey
   */
  function createObjectFromDict(array $dict): JwtApiKey {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new JwtApiKey($dict['jwtapikeyid'], $dict['startvalid'], $dict['endvalid'], $dict['userid'], $dict['isrevoked']);
  }
}
