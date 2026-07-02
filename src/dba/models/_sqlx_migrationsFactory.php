<?php

namespace Hashtopolis\dba\models;

use Exception;
use Hashtopolis\dba\AbstractModelFactory;
use Hashtopolis\dba\Util;

class _sqlx_migrationsFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "_sqlx_migrations";
  }
  
  function getModelTable(): string {
    return "_sqlx_migrations";
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
   * @return _sqlx_migrations
   */
  function getNullObject(): _sqlx_migrations {
    return new _sqlx_migrations(-1, null, null, null, null, null);
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return _sqlx_migrations
   */
  function createObjectFromDict($pk, $dict): _sqlx_migrations {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    if (is_resource($dict['checksum'])) {
      $t = stream_get_contents($dict['checksum']);
      fclose($dict['checksum']);
      $dict['checksum'] = bin2hex($t);
    }
    return new _sqlx_migrations($dict['version'], $dict['description'], $dict['installed_on'], $dict['success'], $dict['checksum'], $dict['execution_time']);
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return _sqlx_migrations|array|null
   * @throws Exception
   */
  function filter(array $options, bool $single = false): _sqlx_migrations|array|null {
    $join = false;
    if (array_key_exists('join', $options)) {
      $join = true;
    }
    if ($single) {
      if ($join) {
        return parent::filter($options, $single);
      }
      return Util::cast(parent::filter($options, $single), _sqlx_migrations::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, _sqlx_migrations::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return ?_sqlx_migrations
   * @throws Exception
   */
  function get($pk): ?_sqlx_migrations {
    return Util::cast(parent::get($pk), _sqlx_migrations::class);
  }
  
  /**
   * @param _sqlx_migrations $model
   * @return _sqlx_migrations
   * @throws Exception
   */
  function save($model): _sqlx_migrations {
    return Util::cast(parent::save($model), _sqlx_migrations::class);
  }
}
