<?php
/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 23.01.17
 * Time: 19:02
 */

/*
 * All define classes should start with 'D'
 */

// hashcat status numbers
class DHashcatStatus{
  const INIT = 0;
  const AUTOTUNE = 1;
  const RUNNING = 2;
  const PAUSED = 3;
  const EXHAUSTED = 4;
  const CRACKED = 5;
  const ABORTED = 6;
  const QUIT = 7;
  const BYPASS = 8;
  const ABORTED_CHECKPOINT = 9;
  const STATUS_ABORTED_RUNTIME = 10;
}

// operating systems
class DOperatingSystem {
  const LINUX = 0;
  const WINDOWS = 1;
  const OSX = 2;
}

// hashlist formats
class DHashlistFormat {
  const PLAIN = 0;
  const WPA = 1;
  const BINARY = 2;
  const SUPERHASHLIST = 3;
}

// access levels for user groups
class DAccessLevel { // if you change any of them here, you need to check if this is consistent with the database
  const VIEW_ONLY = 1;
  const READ_ONLY = 5;
  const USER = 20;
  const SUPERUSER = 30;
  const ADMINISTRATOR = 50;
}

// used config values
class DConfig {
  const BENCHMARK_TIME = "benchtime";
  const CHUNK_DURATION = "chunktime";
  const CHUNK_TIMEOUT = "chunktimeout";
  const AGENT_TIMEOUT = "agenttimeout";
  const HASHES_PAGE_SIZE = "pagingSize";
  const FIELD_SEPARATOR = "fieldseparator";
  const HASHLIST_ALIAS = "hashlistAlias";
  const STATUS_TIMER = "statustimer";
  const BLACKLIST_CHARS = "blacklistChars";
  const NUMBER_LOGENTRIES = "numLogEntries";
  
  /**
   * @param $config string
   * @return string
   */
  public static function getConfigType($config){
    switch($config){
      case DConfig::BENCHMARK_TIME:
        return DConfigType::NUMBER_INPUT;
      case DConfig::CHUNK_DURATION:
        return DConfigType::NUMBER_INPUT;
      case DConfig::CHUNK_TIMEOUT:
        return DConfigType::NUMBER_INPUT;
      case DConfig::AGENT_TIMEOUT:
        return DConfigType::NUMBER_INPUT;
      case DConfig::HASHES_PAGE_SIZE:
        return DConfigType::NUMBER_INPUT;
      case DConfig::FIELD_SEPARATOR:
        return DConfigType::STRING_INPUT;
      case DConfig::HASHLIST_ALIAS:
        return DConfigType::STRING_INPUT;
      case DConfig::STATUS_TIMER:
        return DConfigType::NUMBER_INPUT;
      case DConfig::BLACKLIST_CHARS:
        return DConfigType::STRING_INPUT;
      case DConfig::NUMBER_LOGENTRIES:
        return DConfigType::NUMBER_INPUT;
    }
    return DConfigType::STRING_INPUT;
  }
}

class DConfigType {
  const STRING_INPUT = "string";
  const NUMBER_INPUT = "number";
}

// log entry types
class DLogEntry {
  const WARN = "warning";
  const ERROR = "error";
  const FATAL = "fatal error";
  const INFO = "information";
}


