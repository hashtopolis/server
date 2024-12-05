<?php

class LockUtils {
  /** @var $locks Lock[] */
  private static $locks = array();
  
  /**
   * @param string $lockFile
   * @throws Exception
   */
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
  
  /**
   * @param string $lockFile
   */
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

  /**
   * Deletes a lock file associated with a specific task ID if it exists.
   * 
   * @param int $taskId The unique identifier of the task associated with the lock file.
   * 
   * @return void
   */
  public static function deleteLockFile($taskId) {
    $lockFile = dirname(__FILE__) . "/locks/" . LOCK::CHUNKING . $taskId;
    if(file_exists($lockFile)) {
      unlink($lockFile);
    }
  }
}