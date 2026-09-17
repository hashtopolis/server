<?php

namespace Hashtopolis\inc\utils;

use Exception;

class LockUtils {
  /** @var Lock[] $locks */
  private static array $locks = [];
  
  /**
   * @param string $lockFile
   * @throws Exception
   */
  public static function get(string $lockFile): void {
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
   * Tries to acquire the given lock without blocking.
   *
   * @param string $lockFile
   * @return bool true if the lock was acquired, false if it is currently held elsewhere
   */
  public static function tryGet(string $lockFile): bool {
    if (isset(self::$locks[$lockFile])) {
      $lock = self::$locks[$lockFile];
    }
    else {
      try {
        $lock = new Lock($lockFile);
      }
      catch (Exception $e) {
        return false;
      }
      self::$locks[$lockFile] = $lock;
    }
    return $lock->tryGetLock();
  }
  
  /**
   * @param string $lockFile
   */
  public static function release(string $lockFile): void {
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
  public static function deleteLockFile(int $taskId): void {
    $lockFile = dirname(__FILE__) . "/locks/" . Lock::CHUNKING . $taskId;
    if (file_exists($lockFile)) {
      unlink($lockFile);
    }
  }
}