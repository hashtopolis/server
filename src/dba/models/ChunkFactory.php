<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModelFactory;

/**
 * @extends AbstractModelFactory<Chunk>
 */
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
}
