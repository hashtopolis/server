<?php

namespace DBA;

class ChunkFactory extends AbstractModelFactory {
  function getModelName() {
    return "Chunk";
  }
  
  function getModelTable() {
    return "Chunk";
  }
  
  function isCachable() {
    return false;
  }
  
  function getCacheValidTime() {
    return -1;
  }
  
  /**
   * @return Chunk
   */
  function getNullObject() {
    $o = new Chunk(-1, null, null, null, null, null, null, null, null, null, null, null);
    return $o;
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return Chunk
   */
  function createObjectFromDict($pk, $dict) {
    $o = new Chunk($dict['chunkId'], $dict['taskId'], $dict['skip'], $dict['length'], $dict['agentId'], $dict['dispatchTime'], $dict['solveTime'], $dict['checkpoint'], $dict['progress'], $dict['state'], $dict['cracked'], $dict['speed']);
    return $o;
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return Chunk|Chunk[]
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
      return Util::cast(parent::filter($options, $single), Chunk::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, Chunk::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return Chunk
   */
  function get($pk) {
    return Util::cast(parent::get($pk), Chunk::class);
  }
  
  /**
   * @param Chunk $model
   * @return Chunk
   */
  function save($model) {
    return Util::cast(parent::save($model), Chunk::class);
  }
}