<?php

namespace DBA;

class AssignmentFactory extends AbstractModelFactory {
  function getModelName() {
    return "Assignment";
  }
  
  function getModelTable() {
    return "Assignment";
  }
  
  function isCachable() {
    return false;
  }
  
  function getCacheValidTime() {
    return -1;
  }
  
  /**
   * @return Assignment
   */
  function getNullObject() {
    $o = new Assignment(-1, null, null, null);
    return $o;
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return Assignment
   */
  function createObjectFromDict($pk, $dict) {
    $o = new Assignment($dict['assignmentId'], $dict['taskId'], $dict['agentId'], $dict['benchmark']);
    return $o;
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return Assignment|Assignment[]
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
      return Util::cast(parent::filter($options, $single), Assignment::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, Assignment::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return Assignment
   */
  function get($pk) {
    return Util::cast(parent::get($pk), Assignment::class);
  }
  
  /**
   * @param Assignment $model
   * @return Assignment
   */
  function save($model) {
    return Util::cast(parent::save($model), Assignment::class);
  }
}