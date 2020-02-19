<?php

namespace DBA;

class SessionFactory extends AbstractModelFactory {
  function getModelName() {
    return "Session";
  }
  
  function getModelTable() {
    return "Session";
  }
  
  function isCachable() {
    return false;
  }
  
  function getCacheValidTime() {
    return -1;
  }
  
  /**
   * @return Session
   */
  function getNullObject() {
    $o = new Session(-1, null, null, null, null, null, null);
    return $o;
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return Session
   */
  function createObjectFromDict($pk, $dict) {
    $o = new Session($dict['sessionId'], $dict['userId'], $dict['sessionStartDate'], $dict['lastActionDate'], $dict['isOpen'], $dict['sessionLifetime'], $dict['sessionKey']);
    return $o;
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return Session|Session[]
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
      return Util::cast(parent::filter($options, $single), Session::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, Session::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return Session
   */
  function get($pk) {
    return Util::cast(parent::get($pk), Session::class);
  }
  
  /**
   * @param Session $model
   * @return Session
   */
  function save($model) {
    return Util::cast(parent::save($model), Session::class);
  }
}