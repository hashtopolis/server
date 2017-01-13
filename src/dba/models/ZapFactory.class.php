<?php

/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 02.01.17
 * Time: 23:57
 */

namespace DBA;

class ZapFactory extends AbstractModelFactory {
  function getModelName() {
    return "Zap";
  }
  
  function getModelTable() {
    return "Zap";
  }
  
  function isCachable() {
    return false;
  }
  
  function getCacheValidTime() {
    return -1;
  }

  /**
   * @return Zap
   */
  function getNullObject() {
    $o = new Zap(-1, null, null, null);
    return $o;
  }

  /**
   * @param string $pk
   * @param array $dict
   * @return Zap
   */
  function createObjectFromDict($pk, $dict) {
    $o = new Zap($pk, $dict['hash'], $dict['solveTime'], $dict['hashlistId']);
    return $o;
  }

  /**
   * @param array $options
   * @param bool $single
   * @return Zap|Zap[]
   */
  function filter($options, $single = false) {
    if($single){
      return Util::cast(parent::filter($options, $single), Zap::class);
    }
    $objects = parent::filter($options, $single);
    $models = array();
    foreach($objects as $object){
      $models[] = Util::cast($object, Zap::class);
    }
    return $models;
  }

  /**
   * @param string $pk
   * @return Zap
   */
  function get($pk) {
    return Util::cast(parent::get($pk), Zap::class);
  }

  /**
   * @param Zap $model
   * @return Zap
   */
  function save($model) {
    return Util::cast(parent::save($model), Zap::class);
  }
}