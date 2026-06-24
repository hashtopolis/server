<?php

namespace Hashtopolis\inc\utils;

use Hashtopolis\inc\HTException;

/**
 * @deprecated
 */
class RunnerUtils {
  /**
   * Start the runner service
   * @throws HTException
   */
  public static function startService() {
    if (!self::isAvailable()) {
      throw new HTException("Cannot start runner because it's not available!");
    }
    $dir = self::getDir();
    $out = [];
    exec("cd '$dir' && python3 runner.zip start", $out);
    if (sizeof($out) > 0) {
      throw new HTException("There maybe was trouble starting the runner: [" . implode(", ", $out) . "]");
    }
  }
  
  /**
   * Get the status of the runner service
   * @param bool $exception
   * @return string
   * @throws HTException
   */
  public static function getStatus($exception = true) {
    if (!self::isAvailable()) {
      if (!$exception) {
        return "Not available!";
      }
      throw new HTException("Cannot get status of runner because it's not available!");
    }
    $dir = self::getDir();
    $out = [];
    exec("cd '$dir' && python3 runner.zip status", $out);
    if (sizeof($out) == 0) {
      return "unknown";
    }
    return $out[0];
  }
  
  /**
   * Stop the runner service
   * @throws HTException
   */
  public static function stopService() {
    if (!self::isAvailable()) {
      throw new HTException("Cannot stop runner because it's not available!");
    }
    $dir = self::getDir();
    $out = [];
    exec("cd '$dir' && python3 runner.zip stop", $out);
  }
  
  /**
   * Tests if the multicast feature is available in terms of having python3 installed and having the python zip to execute
   * @return boolean
   */
  private static function isAvailable() {
    if (!shell_exec("which python3")) {
      return false;
    }
    $path = self::getDir() . "/runner.zip";
    $uftp = self::getDir() . "/uftp";
    if (!file_exists($path)) {
      return false;
    }
    else if (!file_exists($uftp)) {
      return false;
    }
    return true;
  }
  
  /**
   * Returns to the directory of the runner
   * @return string
   */
  private static function getDir() {
    return dirname(__FILE__) . "/../runner";
  }
}