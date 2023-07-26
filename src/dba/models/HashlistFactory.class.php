<?php

namespace DBA;

class HashlistFactory extends AbstractModelFactory {
  function getModelName() {
    return "Hashlist";
  }
  
  function getModelTable() {
    return "Hashlist";
  }
  
  function isCachable() {
    return false;
  }
  
  function getCacheValidTime() {
    return -1;
  }
  
  /**
   * @return Hashlist
   */
  function getNullObject() {
    $o = new Hashlist(-1, null, null, null, null, null, null, null, null, null, null, null, null, null, null);
    return $o;
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return Hashlist
   */
  function createObjectFromDict($pk, $dict) {
    $o = new Hashlist($dict['hashlistId'], $dict['hashlistName'], $dict['format'], $dict['hashTypeId'], $dict['hashCount'], $dict['saltSeparator'], $dict['cracked'], $dict['isSecret'], $dict['hexSalt'], $dict['isSalted'], $dict['accessGroupId'], $dict['notes'], $dict['brainId'], $dict['brainFeatures'], $dict['isArchived']);
    return $o;
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return Hashlist|Hashlist[]
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
      return Util::cast(parent::filter($options, $single), Hashlist::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, Hashlist::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return Hashlist
   */
  function get($pk) {
    return Util::cast(parent::get($pk), Hashlist::class);
  }
  
  /**
   * @param Hashlist $model
   * @return Hashlist
   */
  function save($model) {
    return Util::cast(parent::save($model), Hashlist::class);
  }
}