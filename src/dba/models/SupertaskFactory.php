<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModelFactory;

/**
 * @extends AbstractModelFactory<Supertask>
 */
class SupertaskFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "Supertask";
  }
  
  function getModelTable(): string {
    return "Supertask";
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
   * @return Supertask
   */
  function getNullObject(): Supertask {
    return new Supertask(-1, null);
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return Supertask
   */
  function createObjectFromDict($pk, $dict): Supertask {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new Supertask($dict['supertaskid'], $dict['supertaskname']);
  }
}
