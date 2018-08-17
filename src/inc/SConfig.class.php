<?php

class SConfig{
  private static $instance = null;

  /**
   * @return DataSet
   */
  public static function getInstance($force = false){
    global $FACTORIES;

    if(self::$instance == null || $force){
      $res = $FACTORIES::getConfigFactory()->filter([]);
      self::$instance = new DataSet();
      foreach ($res as $entry) {
        self::$instance->addValue($entry->getItem(), $entry->getValue());
      }
    }
    return self::$instance;
  }

  /**
   * Force reloading the config from the database
   */
  public static function reload(){
    SConfig::getInstance(true);
  }
}