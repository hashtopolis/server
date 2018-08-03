<?php

class LockUtils {
  /** @var $locks Lock[] */
  private static $locks = array();
  
  public static function get($lockFile) {
    $lock = null;
    if (isset(self::$locks[$lockFile])) {
      $lock = self::$locks[$lockFile];
    }
    else {
      $lock = new Lock($lockFile);
      self::$locks[$lockFile] = $lock;
    }
    
    // get lock and block
    try {
      $lock->getLock();
    }
    catch (Exception $e) {
      die("Locking: " . $e->getMessage());
    }
  }
  
  public static function release($lockFile) {
    if (isset(self::$locks[$lockFile])) {
      $lock = self::$locks[$lockFile];
      try {
        $lock->release();
      }
      catch (Exception $e) {
        die("Locking: " . $e->getMessage());
      }
    }
  }
}