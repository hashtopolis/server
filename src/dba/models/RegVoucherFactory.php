<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModelFactory;
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
   * @return RegVoucher|RegVoucher[]
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
   */
  function get($pk): ?RegVoucher {
    return Util::cast(parent::get($pk), RegVoucher::class);
  }
  
  /**
   * @param RegVoucher $model
   * @return RegVoucher
   */
  function save($model): RegVoucher {
    return Util::cast(parent::save($model), RegVoucher::class);
  }
}
