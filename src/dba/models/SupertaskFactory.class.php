<?php

namespace DBA;

class SupertaskFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "Supertask";
  }
  
  function getModelTable(): string {
    return "Supertask";
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
    return new Supertask($dict['supertaskId'], $dict['supertaskName']);
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return Supertask|Supertask[]
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