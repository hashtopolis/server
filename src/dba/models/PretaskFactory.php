<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModelFactory;

/**
 * @extends AbstractModelFactory<Pretask>
 */
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
   * @param array $dict
   * @return Pretask
   */
  function createObjectFromDict(array $dict): Pretask {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new Pretask($dict['pretaskid'], $dict['taskname'], $dict['attackcmd'], $dict['chunktime'], $dict['statustimer'], $dict['color'], $dict['issmall'], $dict['iscputask'], $dict['usenewbench'], $dict['priority'], $dict['maxagents'], $dict['ismaskimport'], $dict['crackerbinarytypeid']);
  }
}
