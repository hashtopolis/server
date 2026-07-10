<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModelFactory;

/**
 * @extends AbstractModelFactory<User>
 */
class UserFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "User";
  }
  
  function getModelTable(): string {
    return "User";
  }

  function isMapping(): bool {
    return True;
  }
  
  function isCachable(): bool {
    return false;
  }
  
  function getCacheValidTime(): int {
    return -1;
  }
  
  /**
   * @return User
   */
  function getNullObject(): User {
    return new User(-1, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null);
  }
  
  /**
   * @param array $dict
   * @return User
   */
  function createObjectFromDict(array $dict): User {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new User($dict['userid'], $dict['username'], $dict['email'], $dict['passwordhash'], $dict['passwordsalt'], $dict['isvalid'], $dict['iscomputedpassword'], $dict['lastlogindate'], $dict['registeredsince'], $dict['sessionlifetime'], $dict['rightgroupid'], $dict['yubikey'], $dict['otp1'], $dict['otp2'], $dict['otp3'], $dict['otp4']);
  }
}
