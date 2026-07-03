<?php

namespace Hashtopolis\dba\models;

use Exception;
use PDOStatement;
use Hashtopolis\dba\AbstractModelFactory;
use Hashtopolis\dba\AbstractModel;
use Hashtopolis\dba\Util;

class PreprocessorFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "Preprocessor";
  }
  
  function getModelTable(): string {
    return "Preprocessor";
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
   * @return Preprocessor
   */
  function getNullObject(): Preprocessor {
    return new Preprocessor(-1, null, null, null, null, null, null);
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return Preprocessor
   */
  function createObjectFromDict($pk, $dict): Preprocessor {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new Preprocessor($dict['preprocessorid'], $dict['name'], $dict['url'], $dict['binaryname'], $dict['keyspacecommand'], $dict['skipcommand'], $dict['limitcommand']);
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return Preprocessor|array|null
   * @throws Exception
   */
  function filter(array $options, bool $single = false): Preprocessor|array|null {
    $join = false;
    if (array_key_exists('join', $options)) {
      $join = true;
    }
    if ($single) {
      if ($join) {
        return parent::filter($options, $single);
      }
      return Util::cast(parent::filter($options, $single), Preprocessor::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, Preprocessor::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return ?Preprocessor
   * @throws Exception
   */
  function get($pk): ?Preprocessor {
    return Util::cast(parent::get($pk), Preprocessor::class);
  }
  
  /**
   * @param Preprocessor $model
   * @return Preprocessor
   * @throws Exception
   */
  function save($model): Preprocessor {
    return Util::cast(parent::save($model), Preprocessor::class);
  }

  /**
   * @param Preprocessor $model
   * @param array $arr key-value associations for update
   * @return Preprocessor
   * @throws Exception
   */
  function mset($model, array $arr): Preprocessor {
    return Util::cast(parent::mset($model, $arr), Preprocessor::class);
  }

  /**
   * @param Preprocessor $model
   * @param string $key key of the column to update
   * @param $value
   * @return Preprocessor
   * @throws Exception
   */
  function set($model, string $key, $value): Preprocessor {
    return Util::cast(parent::set($model, $key, $value), Preprocessor::class);
  }
}
