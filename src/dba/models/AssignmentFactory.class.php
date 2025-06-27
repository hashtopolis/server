<?php

namespace DBA;

class AssignmentFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "Assignment";
  }
  
  function getModelTable(): string {
    return "Assignment";
  }
  
  function isCachable(): bool {
    return false;
  }
  
  function getCacheValidTime(): int {
    return -1;
  }
  
  /**
   * @return Assignment
   */
  function getNullObject(): Assignment {
    return new Assignment(-1, null, null, null);
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return Assignment
   */
  function createObjectFromDict($pk, $dict): Assignment {
    return new Assignment($dict['assignmentId'], $dict['taskId'], $dict['agentId'], $dict['benchmark']);
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
   * @return ?Assignment
   */
  function get($pk): ?Assignment {
    return Util::cast(parent::get($pk), Assignment::class);
  }
  
  /**
   * @param Assignment $model
   * @return Assignment
   */
  function save($model): Assignment {
    return Util::cast(parent::save($model), Assignment::class);
  }
}