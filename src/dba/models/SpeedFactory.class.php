<?php

namespace DBA;

class SpeedFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "Speed";
  }
  
  function getModelTable(): string {
    return "Speed";
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
   * @return Speed
   */
  function getNullObject(): Speed {
    return new Speed(-1, null, null, null, null);
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return Speed
   */
  function createObjectFromDict($pk, $dict): Speed {
    return new Speed($dict['speedId'], $dict['agentId'], $dict['taskId'], $dict['speed'], $dict['time']);
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return Speed|Speed[]
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
      return Util::cast(parent::filter($options, $single), Speed::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, Speed::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return ?Speed
   */
  function get($pk): ?Speed {
    return Util::cast(parent::get($pk), Speed::class);
  }
  
  /**
   * @param Speed $model
   * @return Speed
   */
  function save($model): Speed {
    return Util::cast(parent::save($model), Speed::class);
  }
}