<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModelFactory;
use Hashtopolis\dba\Util;

class SupertaskFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "Supertask";
  }
  
  function getModelTable(): string {
    return "Supertask";
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
   * @return Supertask
   */
  function getNullObject(): Supertask {
    return new Supertask(-1, null);
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return Supertask
   */
  function createObjectFromDict($pk, $dict): Supertask {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new Supertask($dict['supertaskid'], $dict['supertaskname']);
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return Supertask|Supertask[]
   */
  function filter(array $options, bool $single = false): Supertask|array|null {
    $join = false;
    if (array_key_exists('join', $options)) {
      $join = true;
    }
    if ($single) {
      if ($join) {
        return parent::filter($options, $single);
      }
      return Util::cast(parent::filter($options, $single), Supertask::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, Supertask::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return ?Supertask
   */
  function get($pk): ?Supertask {
    return Util::cast(parent::get($pk), Supertask::class);
  }
  
  /**
   * @param Supertask $model
   * @return Supertask
   */
  function save($model): Supertask {
    return Util::cast(parent::save($model), Supertask::class);
  }
}
