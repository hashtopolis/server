<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModelFactory;
use Hashtopolis\dba\Util;

class HashlistHashlistFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "HashlistHashlist";
  }
  
  function getModelTable(): string {
    return "HashlistHashlist";
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
   * @return HashlistHashlist
   */
  function getNullObject(): HashlistHashlist {
    return new HashlistHashlist(-1, null, null);
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return HashlistHashlist
   */
  function createObjectFromDict($pk, $dict): HashlistHashlist {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new HashlistHashlist($dict['hashlisthashlistid'], $dict['parenthashlistid'], $dict['hashlistid']);
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return HashlistHashlist|HashlistHashlist[]
   */
  function filter(array $options, bool $single = false): HashlistHashlist|array|null {
    $join = false;
    if (array_key_exists('join', $options)) {
      $join = true;
    }
    if ($single) {
      if ($join) {
        return parent::filter($options, $single);
      }
      return Util::cast(parent::filter($options, $single), HashlistHashlist::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, HashlistHashlist::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return ?HashlistHashlist
   */
  function get($pk): ?HashlistHashlist {
    return Util::cast(parent::get($pk), HashlistHashlist::class);
  }
  
  /**
   * @param HashlistHashlist $model
   * @return HashlistHashlist
   */
  function save($model): HashlistHashlist {
    return Util::cast(parent::save($model), HashlistHashlist::class);
  }
}
