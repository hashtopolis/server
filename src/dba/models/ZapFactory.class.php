<?php

namespace DBA;

class ZapFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "Zap";
  }
  
  function getModelTable(): string {
    return "Zap";
  }
  
  function isCachable(): bool {
    return false;
  }
  
  function getCacheValidTime(): int {
    return -1;
  }
  
  /**
   * @return Zap
   */
  function getNullObject(): Zap {
    return new Zap(-1, null, null, null, null);
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return Zap
   */
  function createObjectFromDict($pk, $dict): Zap {
    return new Zap($dict['zapId'], $dict['hash'], $dict['solveTime'], $dict['agentId'], $dict['hashlistId']);
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return Zap|Zap[]
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
      return Util::cast(parent::filter($options, $single), Zap::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, Zap::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return ?Zap
   */
  function get($pk): ?Zap {
    return Util::cast(parent::get($pk), Zap::class);
  }
  
  /**
   * @param Zap $model
   * @return Zap
   */
  function save($model): Zap {
    return Util::cast(parent::save($model), Zap::class);
  }
}