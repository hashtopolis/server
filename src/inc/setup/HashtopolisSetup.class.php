<?php

abstract class HashtopolisSetup {
  protected static $identifier  = "undefined";
  protected static $type        = "undefined";
  protected static $description = "";
  
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
  
  public function getIdentifier() {
    return self::$identifier;
  }
  
  public function getSetupType() {
    return self::$type;
  }
  
  public function getDescription() {
    return self::$description;
  }
  
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
