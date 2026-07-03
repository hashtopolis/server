<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModelFactory;

/**
 * @extends AbstractModelFactory<SupertaskPretask>
 */
class SupertaskPretaskFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "SupertaskPretask";
  }
  
  function getModelTable(): string {
    return "SupertaskPretask";
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
   * @return SupertaskPretask
   */
  function getNullObject(): SupertaskPretask {
    return new SupertaskPretask(-1, null, null);
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return SupertaskPretask
   */
  function createObjectFromDict($pk, $dict): SupertaskPretask {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new SupertaskPretask($dict['supertaskpretaskid'], $dict['supertaskid'], $dict['pretaskid']);
  }
}
