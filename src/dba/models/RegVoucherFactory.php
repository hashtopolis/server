<?php

namespace Hashtopolis\dba\models;

use Exception;
use PDOStatement;
use Hashtopolis\dba\AbstractModelFactory;
use Hashtopolis\dba\AbstractModel;
use Hashtopolis\dba\Util;

class RegVoucherFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "RegVoucher";
  }
  
  function getModelTable(): string {
    return "RegVoucher";
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
   * @return RegVoucher
   */
  function getNullObject(): RegVoucher {
    return new RegVoucher(-1, null, null);
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return RegVoucher
   */
  function createObjectFromDict($pk, $dict): RegVoucher {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new RegVoucher($dict['regvoucherid'], $dict['voucher'], $dict['time']);
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return RegVoucher|array|null
   * @throws Exception
   */
  function filter(array $options, bool $single = false): RegVoucher|array|null {
    $join = false;
    if (array_key_exists('join', $options)) {
      $join = true;
    }
    if ($single) {
      if ($join) {
        return parent::filter($options, $single);
      }
      return Util::cast(parent::filter($options, $single), RegVoucher::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, RegVoucher::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return ?RegVoucher
   * @throws Exception
   */
  function get($pk): ?RegVoucher {
    return Util::cast(parent::get($pk), RegVoucher::class);
  }
  
  /**
   * @param RegVoucher $model
   * @return RegVoucher
   * @throws Exception
   */
  function save($model): RegVoucher {
    return Util::cast(parent::save($model), RegVoucher::class);
  }

  /**
   * @param RegVoucher $model
   * @param array $arr key-value associations for update
   * @return RegVoucher
   * @throws Exception
   */
  function mset($model, array $arr): RegVoucher {
    return Util::cast(parent::mset($model, $arr), RegVoucher::class);
  }

  /**
   * @param RegVoucher $model
   * @param string $key key of the column to update
   * @param $value
   * @return RegVoucher
   * @throws Exception
   */
  function set($model, string $key, $value): RegVoucher {
    return Util::cast(parent::set($model, $key, $value), RegVoucher::class);
  }
}
