<?php

namespace DBA;

class StoredValueFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "StoredValue";
  }
  
  function getModelTable(): string {
    return "StoredValue";
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
   * @return StoredValue
   */
  function getNullObject(): StoredValue {
    return new StoredValue(-1, null);
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return StoredValue
   */
  function createObjectFromDict($pk, $dict): StoredValue {
    return new StoredValue($dict['storedValueId'], $dict['val']);
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return StoredValue|StoredValue[]
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
      return Util::cast(parent::filter($options, $single), StoredValue::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, StoredValue::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return ?StoredValue
   */
  function get($pk): ?StoredValue {
    return Util::cast(parent::get($pk), StoredValue::class);
  }
  
  /**
   * @param StoredValue $model
   * @return StoredValue
   */
  function save($model): StoredValue {
    return Util::cast(parent::save($model), StoredValue::class);
  }
}