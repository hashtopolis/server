<?php

namespace DBA;

class AgentErrorFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "AgentError";
  }
  
  function getModelTable(): string {
    return "AgentError";
  }
  
  function isCachable(): bool {
    return false;
  }
  
  function getCacheValidTime(): int {
    return -1;
  }
  
  /**
   * @return AgentError
   */
  function getNullObject(): AgentError {
    return new AgentError(-1, null, null, null, null, null);
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return AgentError
   */
  function createObjectFromDict($pk, $dict): AgentError {
    return new AgentError($dict['agentErrorId'], $dict['agentId'], $dict['taskId'], $dict['chunkId'], $dict['time'], $dict['error']);
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return AgentError|AgentError[]
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
      return Util::cast(parent::filter($options, $single), AgentError::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, AgentError::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return ?AgentError
   */
  function get($pk): ?AgentError {
    return Util::cast(parent::get($pk), AgentError::class);
  }
  
  /**
   * @param AgentError $model
   * @return AgentError
   */
  function save($model): AgentError {
    return Util::cast(parent::save($model), AgentError::class);
  }
}