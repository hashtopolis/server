<?php

namespace DBA;

class SupertaskPretaskFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "SupertaskPretask";
  }
  
  function getModelTable(): string {
    return "SupertaskPretask";
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
   * @return SupertaskPretask
   */
  function getNullObject(): SupertaskPretask {
    return new SupertaskPretask(-1, null, null);
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return SupertaskPretask
   */
  function createObjectFromDict($pk, $dict): SupertaskPretask {
    return new SupertaskPretask($dict['supertaskPretaskId'], $dict['supertaskId'], $dict['pretaskId']);
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return SupertaskPretask|SupertaskPretask[]
   */
  function filter(array $options, bool $single = false) {
    $join = false;
    if (array_key_exists('join', $options)) {
      $join = true;
    }
    if ($single) {
      if ($join) {
        return parent::filter($options, $single);
      }
      return Util::cast(parent::filter($options, $single), SupertaskPretask::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, SupertaskPretask::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return ?SupertaskPretask
   */
  function get($pk): ?SupertaskPretask {
    return Util::cast(parent::get($pk), SupertaskPretask::class);
  }
  
  /**
   * @param SupertaskPretask $model
   * @return SupertaskPretask
   */
  function save($model): SupertaskPretask {
    return Util::cast(parent::save($model), SupertaskPretask::class);
  }
}