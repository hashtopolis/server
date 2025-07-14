<?php

namespace DBA;

class AccessGroupAgentFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "AccessGroupAgent";
  }
  
  function getModelTable(): string {
    return "AccessGroupAgent";
  }
  
  function isCachable(): bool {
    return false;
  }
  
  function getCacheValidTime(): int {
    return -1;
  }
  
  /**
   * @return AccessGroupAgent
   */
  function getNullObject(): AccessGroupAgent {
    return new AccessGroupAgent(-1, null, null);
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return AccessGroupAgent
   */
  function createObjectFromDict($pk, $dict): AccessGroupAgent {
    return new AccessGroupAgent($dict['accessGroupAgentId'], $dict['accessGroupId'], $dict['agentId']);
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return AccessGroupAgent|AccessGroupAgent[]
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
      return Util::cast(parent::filter($options, $single), AccessGroupAgent::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, AccessGroupAgent::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return ?AccessGroupAgent
   */
  function get($pk): ?AccessGroupAgent {
    return Util::cast(parent::get($pk), AccessGroupAgent::class);
  }
  
  /**
   * @param AccessGroupAgent $model
   * @return AccessGroupAgent
   */
  function save($model): AccessGroupAgent {
    return Util::cast(parent::save($model), AccessGroupAgent::class);
  }
}