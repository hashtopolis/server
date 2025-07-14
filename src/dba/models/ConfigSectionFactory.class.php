<?php

namespace DBA;

class ConfigSectionFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "ConfigSection";
  }
  
  function getModelTable(): string {
    return "ConfigSection";
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
    return new ConfigSection($dict['configSectionId'], $dict['sectionName']);
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return ConfigSection|ConfigSection[]
   */
  function filter($options, $single = false) {
    $join = false;
    if (array_key_exists('join', $options)) {
      $join = true;
    }
    if ($single) {
      if ($join) {
        return parent::filter($options, $single);
      }
      return Util::cast(parent::filter($options, $single), ConfigSection::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, ConfigSection::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return ?ConfigSection
   */
  function get($pk): ?ConfigSection {
    return Util::cast(parent::get($pk), ConfigSection::class);
  }
  
  /**
   * @param ConfigSection $model
   * @return ConfigSection
   */
  function save($model): ConfigSection {
    return Util::cast(parent::save($model), ConfigSection::class);
  }
}