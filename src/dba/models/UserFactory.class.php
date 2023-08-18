<?php

namespace DBA;

class UserFactory extends AbstractModelFactory {
  function getModelName() {
    return "User";
  }
  
  function getModelTable() {
    return "User";
  }
  
  function isCachable() {
    return false;
  }
  
  function getCacheValidTime() {
    return -1;
  }
  
  /**
   * @return User
   */
  function getNullObject() {
    $o = new User(-1, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null);
    return $o;
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return User
   */
  function createObjectFromDict($pk, $dict) {
    $o = new User($dict['userId'], $dict['username'], $dict['email'], $dict['passwordHash'], $dict['passwordSalt'], $dict['isValid'], $dict['isLDAP'], $dict['isComputedPassword'], $dict['lastLoginDate'], $dict['registeredSince'], $dict['sessionLifetime'], $dict['rightGroupId'], $dict['yubikey'], $dict['otp1'], $dict['otp2'], $dict['otp3'], $dict['otp4']);
    return $o;
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return User|User[]
   */
  function filter($options, $single = false) {
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
   * @return User
   */
  function get($pk) {
    return Util::cast(parent::get($pk), User::class);
  }
  
  /**
   * @param User $model
   * @return User
   */
  function save($model) {
    return Util::cast(parent::save($model), User::class);
  }
}