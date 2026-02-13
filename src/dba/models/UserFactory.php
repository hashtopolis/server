<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModelFactory;
use Hashtopolis\dba\Util;

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
   * @param string $pk
   * @param array $dict
   * @return User
   */
  function createObjectFromDict($pk, $dict): User {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new User($dict['userid'], $dict['username'], $dict['email'], $dict['passwordhash'], $dict['passwordsalt'], $dict['isvalid'], $dict['iscomputedpassword'], $dict['lastlogindate'], $dict['registeredsince'], $dict['sessionlifetime'], $dict['rightgroupid'], $dict['yubikey'], $dict['otp1'], $dict['otp2'], $dict['otp3'], $dict['otp4']);
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return User|User[]
   */
  function filter(array $options, bool $single = false): User|array {
    $join = false;
    if (array_key_exists('join', $options)) {
      $join = true;
    }
    if ($single) {
      if ($join) {
        return parent::filter($options, $single);
      }
      return Util::cast(parent::filter($options, $single), User::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, User::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return ?User
   */
  function get($pk): ?User {
    return Util::cast(parent::get($pk), User::class);
  }
  
  /**
   * @param User $model
   * @return User
   */
  function save($model): User {
    return Util::cast(parent::save($model), User::class);
  }
}
