<?php

namespace DBA;

class FilePretaskFactory extends AbstractModelFactory {
  function getModelName() {
    return "FilePretask";
  }
  
  function getModelTable() {
    return "FilePretask";
  }
  
  function isCachable() {
    return false;
  }
  
  function getCacheValidTime() {
    return -1;
  }
  
  /**
   * @return FilePretask
   */
  function getNullObject() {
    $o = new FilePretask(-1, null, null);
    return $o;
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return FilePretask
   */
  function createObjectFromDict($pk, $dict) {
    $o = new FilePretask($dict['filePretaskId'], $dict['fileId'], $dict['pretaskId']);
    return $o;
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return FilePretask|FilePretask[]
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
      return Util::cast(parent::filter($options, $single), FilePretask::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, FilePretask::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return FilePretask
   */
  function get($pk) {
    return Util::cast(parent::get($pk), FilePretask::class);
  }
  
  /**
   * @param FilePretask $model
   * @return FilePretask
   */
  function save($model) {
    return Util::cast(parent::save($model), FilePretask::class);
  }
}