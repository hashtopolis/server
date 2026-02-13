<?php

namespace Hashtopolis\inc\utils;

use Exception;

class Lock {
  const CHUNKING = "chunking.lock";
  const LOG      = "log.lock";
  
  private $lockFile;
  private $lock;
  
  /**
   * Lock constructor.
   * @param $lockFile
   * @throws Exception
   */
  public function __construct($lockFile) {
    $lockFile = dirname(__FILE__) . "/locks/" . $lockFile;
    $lock = fopen($lockFile, 'w');
    if ($lock === false) {
      throw new Exception("Could not open lockfile '$lockFile'!");
    }
    $this->lockFile = $lockFile;
    $this->lock = $lock;
  }
  
  /**
   * @throws Exception
   */
  public function getLock() {
    // Get exclusive lock (blocking)
    $ret = flock($this->lock, LOCK_EX);
    if ($ret === false) {
      throw new Exception("Could not get lock on lockfile '" . $this->lockFile . "'!");
    }
  }
  
  /**
   * @throws Exception
   */
  public function release() {
    /* Release the lock */
    $ret = flock($this->lock, LOCK_UN);
    if ($ret === false) {
      throw new Exception("Could not release lock on lockfile '" . $this->lockFile . "'.");
    }
    // not closing as otherwise we cannot reuse this object for locking
    // fclose($this->lock);
  }
}