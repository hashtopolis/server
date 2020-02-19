<?php

namespace DBA;

class SupertaskFactory extends AbstractModelFactory {
  function getModelName() {
    return "Supertask";
  }
  
  function getModelTable() {
    return "Supertask";
  }
  
  function isCachable() {
    return false;
  }
  
  function getCacheValidTime() {
    return -1;
  }
  
  /**
   * @return Supertask
   */
  function getNullObject() {
    $o = new Supertask(-1, null);
    return $o;
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return Supertask
   */
  function createObjectFromDict($pk, $dict) {
    $o = new Supertask($dict['supertaskId'], $dict['supertaskName']);
    return $o;
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return Supertask|Supertask[]
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
      return Util::cast(parent::filter($options, $single), Supertask::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, Supertask::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return Supertask
   */
  function get($pk) {
    return Util::cast(parent::get($pk), Supertask::class);
  }
  
  /**
   * @param Supertask $model
   * @return Supertask
   */
  function save($model) {
    return Util::cast(parent::save($model), Supertask::class);
  }
}