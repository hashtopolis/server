<?php

namespace DBA;

class AgentStatFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "AgentStat";
  }
  
  function getModelTable(): string {
    return "AgentStat";
  }
  
  function isCachable(): bool {
    return false;
  }
  
  function getCacheValidTime(): int {
    return -1;
  }
  
  /**
   * @return AgentStat
   */
  function getNullObject(): AgentStat {
    return new AgentStat(-1, null, null, null, null);
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return AgentStat
   */
  function createObjectFromDict($pk, $dict): AgentStat {
    return new AgentStat($dict['agentStatId'], $dict['agentId'], $dict['statType'], $dict['time'], $dict['value']);
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return AgentStat|AgentStat[]
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
      return Util::cast(parent::filter($options, $single), AgentStat::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, AgentStat::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return ?AgentStat
   */
  function get($pk): ?AgentStat {
    return Util::cast(parent::get($pk), AgentStat::class);
  }
  
  /**
   * @param AgentStat $model
   * @return AgentStat
   */
  function save($model): AgentStat {
    return Util::cast(parent::save($model), AgentStat::class);
  }
}