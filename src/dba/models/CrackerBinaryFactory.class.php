<?php

namespace DBA;

class CrackerBinaryFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "CrackerBinary";
  }
  
  function getModelTable(): string {
    return "CrackerBinary";
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
   * @return CrackerBinary
   */
  function getNullObject(): CrackerBinary {
    return new CrackerBinary(-1, null, null, null, null);
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return CrackerBinary
   */
  function createObjectFromDict($pk, $dict): CrackerBinary {
    return new CrackerBinary($dict['crackerBinaryId'], $dict['crackerBinaryTypeId'], $dict['version'], $dict['downloadUrl'], $dict['binaryName']);
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return CrackerBinary|CrackerBinary[]
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
      return Util::cast(parent::filter($options, $single), CrackerBinary::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, CrackerBinary::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return ?CrackerBinary
   */
  function get($pk): ?CrackerBinary {
    return Util::cast(parent::get($pk), CrackerBinary::class);
  }
  
  /**
   * @param CrackerBinary $model
   * @return CrackerBinary
   */
  function save($model): CrackerBinary {
    return Util::cast(parent::save($model), CrackerBinary::class);
  }
}