<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModelFactory;

/**
 * @extends AbstractModelFactory<ConfigSection>
 */
class ConfigSectionFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "ConfigSection";
  }
  
  function getModelTable(): string {
    return "ConfigSection";
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
   * @return ConfigSection
   */
  function getNullObject(): ConfigSection {
    return new ConfigSection(-1, null);
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return ConfigSection
   */
  function createObjectFromDict($pk, $dict): ConfigSection {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new ConfigSection($dict['configsectionid'], $dict['sectionname']);
  }
}
