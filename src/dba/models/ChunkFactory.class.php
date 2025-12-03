<?php

namespace DBA;

class ChunkFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "Chunk";
  }
  
  function getModelTable(): string {
    return "Chunk";
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
   * @return Chunk
   */
  function getNullObject(): Chunk {
    return new Chunk(-1, null, null, null, null, null, null, null, null, null, null, null);
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return Chunk
   */
  function createObjectFromDict($pk, $dict): Chunk {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new Chunk($dict['chunkid'], $dict['taskid'], $dict['skip'], $dict['length'], $dict['agentid'], $dict['dispatchtime'], $dict['solvetime'], $dict['checkpoint'], $dict['progress'], $dict['state'], $dict['cracked'], $dict['speed']);
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return Chunk|Chunk[]
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
   * @return ?Chunk
   */
  function get($pk): ?Chunk {
    return Util::cast(parent::get($pk), Chunk::class);
  }
  
  /**
   * @param Chunk $model
   * @return Chunk
   */
  function save($model): Chunk {
    return Util::cast(parent::save($model), Chunk::class);
  }
}
