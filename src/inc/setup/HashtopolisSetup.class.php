<?php

abstract class HashtopolisSetup {
  protected static $applicableTested = false;
  protected static $applicableCache  = false;
  
  private static $instances = [];
  
  public static function add($name, $instance) {
    self::$instances[$name] = $instance;
  }
  
  /**
   * @return HashtopolisSetup[]
   */
  public static function getInstances() {
    return self::$instances;
  }
  
  abstract function getIdentifier();
  
  abstract function getSetupType();
  
  abstract function getDescription();
  
  protected function isApplicableTested() {
    return self::$applicableTested;
  }
  
  protected function getApplicableTestCache() {
    return self::$applicableCache;
  }
  
  protected function setApplicableResult($flag) {
    self::$applicableTested = true;
    self::$applicableCache = $flag;
  }
  
  /**
   * Tests if this specific action is applicable to the current setup in the database.
   * @return bool
   */
  public abstract function isApplicable();
  
  /**
   * Executes this specific action
   * @param $options array
   * @return bool true on success
   */
  public abstract function execute($options);
}
