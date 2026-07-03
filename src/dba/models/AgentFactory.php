<?php

namespace Hashtopolis\dba\models;

use Exception;
use PDOStatement;
use Hashtopolis\dba\AbstractModelFactory;
use Hashtopolis\dba\AbstractModel;
use Hashtopolis\dba\Util;

class AgentFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "Agent";
  }
  
  function getModelTable(): string {
    return "Agent";
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
   * @return Agent
   */
  function getNullObject(): Agent {
    return new Agent(-1, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null);
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return Agent
   */
  function createObjectFromDict($pk, $dict): Agent {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new Agent($dict['agentid'], $dict['agentname'], $dict['uid'], $dict['os'], $dict['devices'], $dict['cmdpars'], $dict['ignoreerrors'], $dict['isactive'], $dict['istrusted'], $dict['token'], $dict['lastact'], $dict['lasttime'], $dict['lastip'], $dict['userid'], $dict['cpuonly'], $dict['clientsignature']);
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return Agent|array|null
   * @throws Exception
   */
  function filter(array $options, bool $single = false): Agent|array|null {
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
   * @return ?Agent
   * @throws Exception
   */
  function get($pk): ?Agent {
    return Util::cast(parent::get($pk), Agent::class);
  }

  /**
   * @param ?Agent $model
   * @param-out ?Agent $model
   * @param array $arr
   * @return ?PDOStatement
   * @throws Exception
   */
  public function mset(?AbstractModel &$model, array $arr): ?PDOStatement {
    $stmt = parent::mset($model, $arr);
    assert($model instanceof Agent);
    return $stmt;
  }

  /**
   * @param ?Agent $model
   * @param-out ?Agent $model
   * @param string $key key of the column to update
   * @param $value
   * @return ?PDOStatement
   * @throws Exception
   */
  public function set(?AbstractModel &$model, string $key, $value): ?PDOStatement {
    $stmt = parent::set($model, $key, $value);
    assert($model instanceof Agent);
    return $stmt;
  }
  
  /**
   * @param Agent $model
   * @return Agent
   * @throws Exception
   */
  function save($model): Agent {
    return Util::cast(parent::save($model), Agent::class);
  }
}
