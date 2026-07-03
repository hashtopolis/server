<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModelFactory;

/**
 * @extends AbstractModelFactory<ApiKey>
 */
class ApiKeyFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "ApiKey";
  }
  
  function getModelTable(): string {
    return "ApiKey";
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
   * @return ApiKey
   */
  function getNullObject(): ApiKey {
    return new ApiKey(-1, null, null, null, null, null, null);
  }
  
  /**
   * @param array $dict
   * @return ApiKey
   */
  function createObjectFromDict(array $dict): ApiKey {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new ApiKey($dict['apikeyid'], $dict['startvalid'], $dict['endvalid'], $dict['accesskey'], $dict['accesscount'], $dict['userid'], $dict['apigroupid']);
  }
}
