<?php

class DServerLog{
  const TRACE   =  0;
  const DEBUG   = 10;
  const INFO    = 20;
  const WARNING = 30;
  const ERROR   = 40;
  const FATAL   = 50;

  public static function log($level, $message){
    if($level >= SConfig::getInstance()->getVal(DConfig::SERVER_LOG_LEVEL)){
      // log it
      LockUtils::get(Lock::LOG);
      $filename = dirname(__FILE__)."/../../log/".date("Y-m-d").".log";
      $line = sprintf("[%s][%-5s]: %s", date("Y-m-d H:i:s T O"), DServerLog::getLevelName($level), $message);
      file_put_contents($filename, $line, FILE_APPEND);
      LockUtils::release(Lock::LOG);
    }
  }

  public static function getLevelName($level){
    switch($level){
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
    return "EMPTY"
  }
}