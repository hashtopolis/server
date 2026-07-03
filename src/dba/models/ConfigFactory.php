<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModelFactory;

/**
 * @extends AbstractModelFactory<Config>
 */
class ConfigFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "Config";
  }
  
  function getModelTable(): string {
    return "Config";
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
   * @return Config
   */
  function getNullObject(): Config {
    return new Config(-1, null, null, null);
  }
  
  /**
   * @param array $dict
   * @return Config
   */
  function createObjectFromDict(array $dict): Config {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new Config($dict['configid'], $dict['configsectionid'], $dict['item'], $dict['value']);
  }
}
