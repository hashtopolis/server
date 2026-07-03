<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModelFactory;

/**
 * @extends AbstractModelFactory<Hashlist>
 */
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
}
