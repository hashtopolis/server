<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModelFactory;
use Hashtopolis\dba\Util;

class AccessGroupUserFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "AccessGroupUser";
  }
  
  function getModelTable(): string {
    return "AccessGroupUser";
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
   * @return AccessGroupUser
   */
  function getNullObject(): AccessGroupUser {
    return new AccessGroupUser(-1, null, null);
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return AccessGroupUser
   */
  function createObjectFromDict($pk, $dict): AccessGroupUser {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new AccessGroupUser($dict['accessgroupuserid'], $dict['accessgroupid'], $dict['userid']);
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return AccessGroupUser|AccessGroupUser[]
   */
  function filter(array $options, bool $single = false): AccessGroupUser|array|null {
    $join = false;
    if (array_key_exists('join', $options)) {
      $join = true;
    }
    if ($single) {
      if ($join) {
        return parent::filter($options, $single);
      }
      return Util::cast(parent::filter($options, $single), AccessGroupUser::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, AccessGroupUser::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return ?AccessGroupUser
   */
  function get($pk): ?AccessGroupUser {
    return Util::cast(parent::get($pk), AccessGroupUser::class);
  }
  
  /**
   * @param AccessGroupUser $model
   * @return AccessGroupUser
   */
  function save($model): AccessGroupUser {
    return Util::cast(parent::save($model), AccessGroupUser::class);
  }
}
