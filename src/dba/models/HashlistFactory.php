<?php

namespace Hashtopolis\dba\models;

use Exception;
use PDOStatement;
use Hashtopolis\dba\AbstractModelFactory;
use Hashtopolis\dba\AbstractModel;
use Hashtopolis\dba\Util;

class HashlistFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "Hashlist";
  }
  
  function getModelTable(): string {
    return "Hashlist";
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
   * @return Hashlist
   */
  function getNullObject(): Hashlist {
    return new Hashlist(-1, null, null, null, null, null, null, null, null, null, null, null, null, null, null);
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return Hashlist
   */
  function createObjectFromDict($pk, $dict): Hashlist {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new Hashlist($dict['hashlistid'], $dict['hashlistname'], $dict['format'], $dict['hashtypeid'], $dict['hashcount'], $dict['saltseparator'], $dict['cracked'], $dict['issecret'], $dict['hexsalt'], $dict['issalted'], $dict['accessgroupid'], $dict['notes'], $dict['brainid'], $dict['brainfeatures'], $dict['isarchived']);
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return Hashlist|array|null
   * @throws Exception
   */
  function filter(array $options, bool $single = false): Hashlist|array|null {
    $join = false;
    if (array_key_exists('join', $options)) {
      $join = true;
    }
    if ($single) {
      if ($join) {
        return parent::filter($options, $single);
      }
      return Util::cast(parent::filter($options, $single), Hashlist::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, Hashlist::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return ?Hashlist
   * @throws Exception
   */
  function get($pk): ?Hashlist {
    return Util::cast(parent::get($pk), Hashlist::class);
  }
  
  /**
   * @param Hashlist $model
   * @return Hashlist
   * @throws Exception
   */
  function save($model): Hashlist {
    return Util::cast(parent::save($model), Hashlist::class);
  }

  /**
   * @param Hashlist $model
   * @param array $arr key-value associations for update
   * @return Hashlist
   * @throws Exception
   */
  function mset($model, array $arr): Hashlist {
    return Util::cast(parent::mset($model, $arr), Hashlist::class);
  }
}
