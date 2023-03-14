<?php

use DBA\Factory;

class DServerLog {
  const TRACE   = 0;
  const DEBUG   = 10;
  const INFO    = 20;
  const WARNING = 30;
  const ERROR   = 40;
  const FATAL   = 50;
  
  public static function log($level, $message, $data = []) {
    if ($level >= SConfig::getInstance()->getVal(DConfig::SERVER_LOG_LEVEL)) {
      // log it
      LockUtils::get(Lock::LOG);
      $filename = Factory::getStoredValueFactory()->get(DDirectories::LOG)->getVal() . "/" . date("Y-m-d") . ".log";
      if (sizeof($data) > 0) {
        $message .= " ###";
        foreach ($data as $d) {
          if (is_object($d) && method_exists($d, "expose")) {
            $d = $d->expose();
          }
          else if (is_object($d)) {
            $d = (array)$d;
          }
          $message .= " " . json_encode($d) . "EOD";
        }
      }
      if (SConfig::getInstance()->getVal(DConfig::SERVER_LOG_LEVEL) <= DServerLog::DEBUG) {
        $key = array_search(__FUNCTION__, array_column(debug_backtrace(), 'function'));
        $file = str_replace('\\', '/', debug_backtrace()[$key]['file']);
        $basePath = str_replace("inc/defines", "", str_replace('\\', '/', dirname(__FILE__)));
        $file = str_replace($basePath, "", $file);
        $lineNum = debug_backtrace()[$key]['line'];
        $line = sprintf("[%s][%-5s][%s:%s]: %s\n", date("Y-m-d H:i:s T O"), DServerLog::getLevelName($level), $file, $lineNum, $message);
      }
      else {
        $line = sprintf("[%s][%-5s]: %s\n", date("Y-m-d H:i:s T O"), DServerLog::getLevelName($level), $message);
      }
      file_put_contents($filename, $line, FILE_APPEND);
      LockUtils::release(Lock::LOG);
    }
  }
  
  public static function getLevelName($level) {
    switch ($level) {
      case DServerLog::TRACE:
        return "TRACE";
      case DServerLog::DEBUG:
        return "DEBUG";
      case DServerLog::INFO:
        return "INFO";
      case DServerLog::WARNING:
        return "WARN";
      case DServerLog::ERROR:
        return "ERROR";
      case DServerLog::FATAL:
        return "FATAL";
    }
    return "EMPTY";
  }
}