<?php

namespace DBA;

class PreprocessorFactory extends AbstractModelFactory {
  function getModelName() {
    return "Preprocessor";
  }
  
  function getModelTable() {
    return "Preprocessor";
  }
  
  function isCachable() {
    return false;
  }
  
  function getCacheValidTime() {
    return -1;
  }
  
  /**
   * @return Preprocessor
   */
  function getNullObject() {
    $o = new Preprocessor(-1, null, null, null, null, null, null);
    return $o;
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return Preprocessor
   */
  function createObjectFromDict($pk, $dict) {
    $o = new Preprocessor($dict['preprocessorId'], $dict['name'], $dict['url'], $dict['binaryName'], $dict['keyspaceCommand'], $dict['skipCommand'], $dict['limitCommand']);
    return $o;
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return Preprocessor|Preprocessor[]
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
      return Util::cast(parent::filter($options, $single), Preprocessor::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, Preprocessor::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return Preprocessor
   */
  function get($pk) {
    return Util::cast(parent::get($pk), Preprocessor::class);
  }
  
  /**
   * @param Preprocessor $model
   * @return Preprocessor
   */
  function save($model) {
    return Util::cast(parent::save($model), Preprocessor::class);
  }
}