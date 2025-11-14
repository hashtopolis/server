<?php

namespace DBA;

class HashTypeFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "HashType";
  }
  
  function getModelTable(): string {
    return "HashType";
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
   * @return HashType
   */
  function getNullObject(): HashType {
    return new HashType(-1, null, null, null);
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return HashType
   */
  function createObjectFromDict($pk, $dict): HashType {
    return new HashType($dict['hashTypeId'], $dict['description'], $dict['isSalted'], $dict['isSlowHash']);
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return HashType|HashType[]
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
      return Util::cast(parent::filter($options, $single), HashType::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, HashType::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return ?HashType
   */
  function get($pk): ?HashType {
    return Util::cast(parent::get($pk), HashType::class);
  }
  
  /**
   * @param HashType $model
   * @return HashType
   */
  function save($model): HashType {
    return Util::cast(parent::save($model), HashType::class);
  }
}