<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModelFactory;

/**
 * @extends AbstractModelFactory<ApiGroup>
 */
class ApiGroupFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "ApiGroup";
  }
  
  function getModelTable(): string {
    return "ApiGroup";
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
   * @return ApiGroup
   */
  function getNullObject(): ApiGroup {
    return new ApiGroup(-1, null, null);
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return ApiGroup
   */
  function createObjectFromDict($pk, $dict): ApiGroup {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new ApiGroup($dict['apigroupid'], $dict['permissions'], $dict['name']);
  }
}
