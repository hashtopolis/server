<?php

namespace Hashtopolis\dba\models;

use Exception;
use PDOStatement;
use Hashtopolis\dba\AbstractModelFactory;
use Hashtopolis\dba\AbstractModel;
use Hashtopolis\dba\Util;

class AgentStatFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "AgentStat";
  }
  
  function getModelTable(): string {
    return "AgentStat";
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
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new AgentStat($dict['agentstatid'], $dict['agentid'], $dict['stattype'], $dict['time'], $dict['value']);
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return AgentStat|array|null
   * @throws Exception
   */
  function filter(array $options, bool $single = false): AgentStat|array|null {
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
   * @throws Exception
   */
  function get($pk): ?AgentStat {
    return Util::cast(parent::get($pk), AgentStat::class);
  }

  /**
   * @param AgentStat $model
   * @param array $arr
   * @return PDOStatement
   * @throws Exception
   */
  function mset(AbstractModel &$model, array $arr): PDOStatement {
    assert($model instanceof AgentStat);
    $stmt = parent::mset($model, $arr);
    assert($model instanceof AgentStat);
    return $stmt;
  }

  /**
   * @param AgentStat $model
   * @param $key string key of the column to update
   * @param $value
   * @return PDOStatement
   * @throws Exception
   */
  function set(AbstractModel &$model, string $key, $value): PDOStatement {
    assert($model instanceof AgentStat);
    $stmt = parent::set($model, $key, $value);
    assert($model instanceof AgentStat);
    return $stmt;
  }
  
  /**
   * @param AgentStat $model
   * @return AgentStat
   * @throws Exception
   */
  function save($model): AgentStat {
    return Util::cast(parent::save($model), AgentStat::class);
  }
}
