<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModelFactory;
use Hashtopolis\dba\Util;

class AgentBinaryFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "AgentBinary";
  }
  
  function getModelTable(): string {
    return "AgentBinary";
  }

  function isMapping(): bool {
    return False;
  }
  
  function isCachable(): bool {
    return false;
  }
  
  function getCacheValidTime(): int {
    return -1;
  }
  
  /**
   * @return AgentBinary
   */
  function getNullObject(): AgentBinary {
    return new AgentBinary(-1, null, null, null, null, null, null);
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return AgentBinary
   */
  function createObjectFromDict($pk, $dict): AgentBinary {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new AgentBinary($dict['agentbinaryid'], $dict['binarytype'], $dict['version'], $dict['operatingsystems'], $dict['filename'], $dict['updatetrack'], $dict['updateavailable']);
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return AgentBinary|AgentBinary[]
   */
  function filter(array $options, bool $single = false): AgentBinary|array|null {
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
   * @return ?AgentBinary
   */
  function get($pk): ?AgentBinary {
    return Util::cast(parent::get($pk), AgentBinary::class);
  }
  
  /**
   * @param AgentBinary $model
   * @return AgentBinary
   */
  function save($model): AgentBinary {
    return Util::cast(parent::save($model), AgentBinary::class);
  }
}
