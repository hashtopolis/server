<?php

namespace Hashtopolis\inc;

use Exception;
use Hashtopolis\dba\Factory;

class SConfig {
  private static ?DataSet $instance = null;
  
  /**
   * @param bool $force
   * @return ?DataSet
   * @throws Exception
   */
  public static function getInstance(bool $force = false): ?DataSet {
    if (self::$instance == null || $force) {
      $res = Factory::getConfigFactory()->filter([]);
      self::$instance = new DataSet();
      foreach ($res as $entry) {
        self::$instance->addValue($entry->getItem(), $entry->getValue());
      }
    }
    return self::$instance;
  }
  
  /**
   * Force reloading the config from the database
   * @throws Exception
   */
  public static function reload(): void {
    SConfig::getInstance(true);
  }
}