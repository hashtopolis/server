<?php

/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 02.01.17
 * Time: 23:57
 */

namespace DBA;

class HashcatReleaseFactory extends AbstractModelFactory {
  function getModelName() {
    return "HashcatRelease";
  }
  
  function getModelTable() {
    return "HashcatRelease";
  }
  
  function isCachable() {
    return false;
  }
  
  function getCacheValidTime() {
    return -1;
  }

  /**
   * @return HashcatRelease
   */
  function getNullObject() {
    $o = new HashcatRelease(-1, null, null, null, null, null, null);
    return $o;
  }

  /**
   * @param string $pk
   * @param array $dict
   * @return HashcatRelease
   */
  function createObjectFromDict($pk, $dict) {
    $o = new HashcatRelease($pk, $dict['version'], $dict['time'], $dict['url'], $dict['commonFiles'], $dict['rootdir'], $dict['minver']);
    return $o;
  }

  /**
   * @param array $options
   * @param bool $single
   * @return HashcatRelease|HashcatRelease[]
   */
  function filter($options, $single = false) {
    if($single){
      return Util::cast(parent::filter($options, $single), HashcatRelease::class);
    }
    $objects = parent::filter($options, $single);
    $models = array();
    foreach($objects as $object){
      $models[] = Util::cast($object, HashcatRelease::class);
    }
    return $models;
  }

  /**
   * @param string $pk
   * @return HashcatRelease
   */
  function get($pk) {
    return Util::cast(parent::get($pk), HashcatRelease::class);
  }

  /**
   * @param HashcatRelease $model
   * @return HashcatRelease
   */
  function save($model) {
    return Util::cast(parent::save($model), HashcatRelease::class);
  }
}