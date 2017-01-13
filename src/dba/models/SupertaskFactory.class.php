<?php

/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 02.01.17
 * Time: 23:57
 */

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
    $o = new Supertask($pk, $dict['supertaskName']);
    return $o;
  }

  /**
   * @param array $options
   * @param bool $single
   * @return Supertask|Supertask[]
   */
  function filter($options, $single = false) {
    if($single){
      return Util::cast(parent::filter($options, $single), Supertask::class);
    }
    $objects = parent::filter($options, $single);
    $models = array();
    foreach($objects as $object){
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