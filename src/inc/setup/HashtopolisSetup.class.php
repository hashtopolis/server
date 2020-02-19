<?php

abstract class HashtopolisSetup {
  protected $applicableTested = false;
  protected $applicableCache  = false;
  
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
    return $this->applicableTested;
  }
  
  protected function getApplicableTestCache() {
    return $this->applicableCache;
  }
  
  protected function setApplicableResult($flag) {
    $this->applicableTested = true;
    $this->applicableCache = $flag;
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
   * @throws HTException
   */
  public abstract function execute($options);
}
