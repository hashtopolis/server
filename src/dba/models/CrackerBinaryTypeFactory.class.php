<?php

namespace DBA;

class CrackerBinaryTypeFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "CrackerBinaryType";
  }
  
  function getModelTable(): string {
    return "CrackerBinaryType";
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
   * @return CrackerBinaryType
   */
  function getNullObject(): CrackerBinaryType {
    return new CrackerBinaryType(-1, null, null);
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return CrackerBinaryType
   */
  function createObjectFromDict($pk, $dict): CrackerBinaryType {
    return new CrackerBinaryType($dict['crackerBinaryTypeId'], $dict['typeName'], $dict['isChunkingAvailable']);
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return CrackerBinaryType|CrackerBinaryType[]
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
      return Util::cast(parent::filter($options, $single), CrackerBinaryType::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, CrackerBinaryType::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return ?CrackerBinaryType
   */
  function get($pk): ?CrackerBinaryType {
    return Util::cast(parent::get($pk), CrackerBinaryType::class);
  }
  
  /**
   * @param CrackerBinaryType $model
   * @return CrackerBinaryType
   */
  function save($model): CrackerBinaryType {
    return Util::cast(parent::save($model), CrackerBinaryType::class);
  }
}