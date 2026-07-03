<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModelFactory;

/**
 * @extends AbstractModelFactory<RegVoucher>
 */
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
   * @param array $dict
   * @return RegVoucher
   */
  function createObjectFromDict(array $dict): RegVoucher {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new RegVoucher($dict['regvoucherid'], $dict['voucher'], $dict['time']);
  }
}
