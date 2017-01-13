<?php

/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 02.01.17
 * Time: 23:57
 */

namespace DBA;

class HashTypeFactory extends AbstractModelFactory {
  function getModelName() {
    return "HashType";
  }
  
  function getModelTable() {
    return "HashType";
  }
  
  function isCachable() {
    return false;
  }
  
  function getCacheValidTime() {
    return -1;
  }

  /**
   * @return HashType
   */
  function getNullObject() {
    $o = new HashType(-1, null);
    return $o;
  }

  /**
   * @param string $pk
   * @param array $dict
   * @return HashType
   */
  function createObjectFromDict($pk, $dict) {
    $o = new HashType($pk, $dict['description']);
    return $o;
  }

  /**
   * @param array $options
   * @param bool $single
   * @return HashType|HashType[]
   */
  function filter($options, $single = false) {
    if($single){
      return Util::cast(parent::filter($options, $single), HashType::class);
    }
    $objects = parent::filter($options, $single);
    $models = array();
    foreach($objects as $object){
      $models[] = Util::cast($object, HashType::class);
    }
    return $models;
  }

  /**
   * @param string $pk
   * @return HashType
   */
  function get($pk) {
    return Util::cast(parent::get($pk), HashType::class);
  }

  /**
   * @param HashType $model
   * @return HashType
   */
  function save($model) {
    return Util::cast(parent::save($model), HashType::class);
  }
}