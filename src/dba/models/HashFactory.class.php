<?php

/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 02.01.17
 * Time: 23:57
 */

namespace DBA;

class HashFactory extends AbstractModelFactory {
  function getModelName() {
    return "Hash";
  }
  
  function getModelTable() {
    return "Hash";
  }
  
  function isCachable() {
    return false;
  }
  
  function getCacheValidTime() {
    return -1;
  }

  /**
   * @return Hash
   */
  function getNullObject() {
    $o = new Hash(-1, null, null, null, null, null, null, null);
    return $o;
  }

  /**
   * @param string $pk
   * @param array $dict
   * @return Hash
   */
  function createObjectFromDict($pk, $dict) {
    $o = new Hash($pk, $dict['hashlistId'], $dict['hash'], $dict['salt'], $dict['plaintext'], $dict['time'], $dict['chunkId'], $dict['isCracked']);
    return $o;
  }

  /**
   * @param array $options
   * @param bool $single
   * @return Hash|Hash[]
   */
  function filter($options, $single = false) {
    if($single){
      return Util::cast(parent::filter($options, $single), Hash::class);
    }
    $objects = parent::filter($options, $single);
    $models = array();
    foreach($objects as $object){
      $models[] = Util::cast($object, Hash::class);
    }
    return $models;
  }

  /**
   * @param string $pk
   * @return Hash
   */
  function get($pk) {
    return Util::cast(parent::get($pk), Hash::class);
  }

  /**
   * @param Hash $model
   * @return Hash
   */
  function save($model) {
    return Util::cast(parent::save($model), Hash::class);
  }
}