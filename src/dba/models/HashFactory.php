<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModelFactory;
use Hashtopolis\dba\Util;

class HashFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "Hash";
  }
  
  function getModelTable(): string {
    return "Hash";
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
   * @return Hash
   */
  function getNullObject(): Hash {
    return new Hash(-1, null, null, null, null, null, null, null, null);
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return Hash
   */
  function createObjectFromDict($pk, $dict): Hash {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new Hash($dict['hashid'], $dict['hashlistid'], $dict['hash'], $dict['salt'], $dict['plaintext'], $dict['timecracked'], $dict['chunkid'], $dict['iscracked'], $dict['crackpos']);
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return Hash|Hash[]
   */
  function filter(array $options, bool $single = false): Hash|array|null {
    $join = false;
    if (array_key_exists('join', $options)) {
      $join = true;
    }
    if ($single) {
      if ($join) {
        return parent::filter($options, $single);
      }
      return Util::cast(parent::filter($options, $single), Hash::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, Hash::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return ?Hash
   */
  function get($pk): ?Hash {
    return Util::cast(parent::get($pk), Hash::class);
  }
  
  /**
   * @param Hash $model
   * @return Hash
   */
  function save($model): Hash {
    return Util::cast(parent::save($model), Hash::class);
  }
}
