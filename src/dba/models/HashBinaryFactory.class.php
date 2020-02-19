<?php

namespace DBA;

class HashBinaryFactory extends AbstractModelFactory {
  function getModelName() {
    return "HashBinary";
  }
  
  function getModelTable() {
    return "HashBinary";
  }
  
  function isCachable() {
    return false;
  }
  
  function getCacheValidTime() {
    return -1;
  }
  
  /**
   * @return HashBinary
   */
  function getNullObject() {
    $o = new HashBinary(-1, null, null, null, null, null, null, null, null);
    return $o;
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return HashBinary
   */
  function createObjectFromDict($pk, $dict) {
    $o = new HashBinary($dict['hashBinaryId'], $dict['hashlistId'], $dict['essid'], $dict['hash'], $dict['plaintext'], $dict['timeCracked'], $dict['chunkId'], $dict['isCracked'], $dict['crackPos']);
    return $o;
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return HashBinary|HashBinary[]
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
      return Util::cast(parent::filter($options, $single), HashBinary::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, HashBinary::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return HashBinary
   */
  function get($pk) {
    return Util::cast(parent::get($pk), HashBinary::class);
  }
  
  /**
   * @param HashBinary $model
   * @return HashBinary
   */
  function save($model) {
    return Util::cast(parent::save($model), HashBinary::class);
  }
}