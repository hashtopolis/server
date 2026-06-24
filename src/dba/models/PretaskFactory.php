<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModelFactory;
use Hashtopolis\dba\Util;

class PretaskFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "Pretask";
  }
  
  function getModelTable(): string {
    return "Pretask";
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
   * @return Pretask
   */
  function getNullObject(): Pretask {
    return new Pretask(-1, null, null, null, null, null, null, null, null, null, null, null, null);
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return Pretask
   */
  function createObjectFromDict($pk, $dict): Pretask {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new Pretask($dict['pretaskid'], $dict['taskname'], $dict['attackcmd'], $dict['chunktime'], $dict['statustimer'], $dict['color'], $dict['issmall'], $dict['iscputask'], $dict['usenewbench'], $dict['priority'], $dict['maxagents'], $dict['ismaskimport'], $dict['crackerbinarytypeid']);
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return Pretask|Pretask[]
   */
  function filter(array $options, bool $single = false): Pretask|array|null {
    $join = false;
    if (array_key_exists('join', $options)) {
      $join = true;
    }
    if ($single) {
      if ($join) {
        return parent::filter($options, $single);
      }
      return Util::cast(parent::filter($options, $single), Pretask::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, Pretask::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return ?Pretask
   */
  function get($pk): ?Pretask {
    return Util::cast(parent::get($pk), Pretask::class);
  }
  
  /**
   * @param Pretask $model
   * @return Pretask
   */
  function save($model): Pretask {
    return Util::cast(parent::save($model), Pretask::class);
  }
}
