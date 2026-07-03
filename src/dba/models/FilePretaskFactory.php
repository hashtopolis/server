<?php

namespace Hashtopolis\dba\models;

use Exception;
use PDOStatement;
use Hashtopolis\dba\AbstractModelFactory;
use Hashtopolis\dba\AbstractModel;
use Hashtopolis\dba\Util;

class FilePretaskFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "FilePretask";
  }
  
  function getModelTable(): string {
    return "FilePretask";
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
   * @return FilePretask
   */
  function getNullObject(): FilePretask {
    return new FilePretask(-1, null, null);
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return FilePretask
   */
  function createObjectFromDict($pk, $dict): FilePretask {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new FilePretask($dict['filepretaskid'], $dict['fileid'], $dict['pretaskid']);
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return FilePretask|array|null
   * @throws Exception
   */
  function filter(array $options, bool $single = false): FilePretask|array|null {
    $join = false;
    if (array_key_exists('join', $options)) {
      $join = true;
    }
    if ($single) {
      if ($join) {
        return parent::filter($options, $single);
      }
      return Util::cast(parent::filter($options, $single), FilePretask::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, FilePretask::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return ?FilePretask
   * @throws Exception
   */
  function get($pk): ?FilePretask {
    return Util::cast(parent::get($pk), FilePretask::class);
  }
  
  /**
   * @param FilePretask $model
   * @return ?FilePretask
   * @throws Exception
   */
  function save($model): ?FilePretask {
    return Util::cast(parent::save($model), FilePretask::class);
  }

  /**
   * @param FilePretask $model
   * @param array $arr key-value associations for update
   * @return FilePretask
   * @throws Exception
   */
  function mset($model, array $arr): FilePretask {
    return Util::cast(parent::mset($model, $arr), FilePretask::class);
  }

  /**
   * @param FilePretask $model
   * @param string $key key of the column to update
   * @param $value
   * @return FilePretask
   * @throws Exception
   */
  function set($model, string $key, $value): FilePretask {
    return Util::cast(parent::set($model, $key, $value), FilePretask::class);
  }
}
