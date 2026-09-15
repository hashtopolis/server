<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModelFactory;

/**
 * @extends AbstractModelFactory<RefreshToken>
 */
class RefreshTokenFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "RefreshToken";
  }
  
  function getModelTable(): string {
    return "RefreshToken";
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
   * @return RefreshToken
   */
  function getNullObject(): RefreshToken {
    return new RefreshToken(-1, null, null, null, null, null, null, null);
  }
  
  /**
   * @param array $dict
   * @return RefreshToken
   */
  function createObjectFromDict(array $dict): RefreshToken {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new RefreshToken($dict['refreshtokenid'], $dict['userid'], $dict['tokenhash'], $dict['familyid'], $dict['issuedat'], $dict['endvalid'], $dict['usedat'], $dict['isrevoked']);
  }
}
