<?php

namespace DBA;

class PretaskFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "Pretask";
  }
  
  function getModelTable(): string {
    return "Pretask";
  }
  
  function isCachable(): bool {
    return false;
  }
  
  function getCacheValidTime(): int {
    return -1;
  }
  
  /**
   * @return Pretask
   */
  function getNullObject(): Pretask {
    return new Pretask(-1, null, null, null, null, null, null, null, null, null, null, null, null);
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return Pretask
   */
  function createObjectFromDict($pk, $dict): Pretask {
    return new Pretask($dict['pretaskId'], $dict['taskName'], $dict['attackCmd'], $dict['chunkTime'], $dict['statusTimer'], $dict['color'], $dict['isSmall'], $dict['isCpuTask'], $dict['useNewBench'], $dict['priority'], $dict['maxAgents'], $dict['isMaskImport'], $dict['crackerBinaryTypeId']);
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return Pretask|Pretask[]
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
      return Util::cast(parent::filter($options, $single), Pretask::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, Pretask::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return ?Pretask
   */
  function get($pk): ?Pretask {
    return Util::cast(parent::get($pk), Pretask::class);
  }
  
  /**
   * @param Pretask $model
   * @return Pretask
   */
  function save($model): Pretask {
    return Util::cast(parent::save($model), Pretask::class);
  }
}