<?php

namespace DBA;

class AgentBinaryFactory extends AbstractModelFactory {
  function getModelName() {
    return "AgentBinary";
  }
  
  function getModelTable() {
    return "AgentBinary";
  }
  
  function isCachable() {
    return false;
  }
  
  function getCacheValidTime() {
    return -1;
  }
  
  /**
   * @return AgentBinary
   */
  function getNullObject() {
    $o = new AgentBinary(-1, null, null, null, null, null, null);
    return $o;
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return AgentBinary
   */
  function createObjectFromDict($pk, $dict) {
    $o = new AgentBinary($dict['agentBinaryId'], $dict['type'], $dict['version'], $dict['operatingSystems'], $dict['filename'], $dict['updateTrack'], $dict['updateAvailable']);
    return $o;
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return AgentBinary|AgentBinary[]
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
      return Util::cast(parent::filter($options, $single), AgentBinary::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, AgentBinary::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return AgentBinary
   */
  function get($pk) {
    return Util::cast(parent::get($pk), AgentBinary::class);
  }
  
  /**
   * @param AgentBinary $model
   * @return AgentBinary
   */
  function save($model) {
    return Util::cast(parent::save($model), AgentBinary::class);
  }
}