<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModelFactory;

/**
 * @extends AbstractModelFactory<_sqlx_migrations>
 */
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
   * @param array $dict
   * @return _sqlx_migrations
   */
  function createObjectFromDict(array $dict): _sqlx_migrations {
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
}
