<?php

namespace DBA;

class AgentFactory extends AbstractModelFactory {
  function getModelName() {
    return "Agent";
  }
  
  function getModelTable() {
    return "Agent";
  }
  
  function isCachable() {
    return false;
  }
  
  function getCacheValidTime() {
    return -1;
  }
  
  /**
   * @return Agent
   */
  function getNullObject() {
    $o = new Agent(-1, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null);
    return $o;
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return Agent
   */
  function createObjectFromDict($pk, $dict) {
    $o = new Agent($dict['agentId'], $dict['agentName'], $dict['uid'], $dict['os'], $dict['devices'], $dict['cmdPars'], $dict['ignoreErrors'], $dict['isActive'], $dict['isTrusted'], $dict['token'], $dict['lastAct'], $dict['lastTime'], $dict['lastIp'], $dict['userId'], $dict['cpuOnly'], $dict['clientSignature']);
    return $o;
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return Agent|Agent[]
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
      return Util::cast(parent::filter($options, $single), Agent::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, Agent::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return Agent
   */
  function get($pk) {
    return Util::cast(parent::get($pk), Agent::class);
  }
  
  /**
   * @param Agent $model
   * @return Agent
   */
  function save($model) {
    return Util::cast(parent::save($model), Agent::class);
  }
}