<?php
/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 23.01.17
 * Time: 19:55
 */

// protocol defines (these are started with 'P')



#####################
# Query definitions #
#####################

abstract class PQuery { // include only generalized query values
  public const QUERY = "query";
  public const ACTION = "action";
  public const TOKEN = "token";
  
  /**
   * This function checks if all required values are given in the query
   *
   * @param $QUERY array the given query
   * @return bool true on valid, false if not
   */
  abstract static function isValid($QUERY);
}

class PQueryLogin extends PQuery {
  static function isValid($QUERY) {
    if(!isset($QUERY[self::TOKEN])){
      return false;
    }
    return true;
  }
}

class PQuerySolve extends PQuery {
  static function isValid($QUERY) {
    if(!isset($QUERY[self::TOKEN]) || !isset($QUERY[self::CHUNK_ID]) || !isset($QUERY[self::KEYSPACE_PROGRESS]) || !isset($QUERY[self::COMBINATION_PROGRESS]) || !isset($QUERY[self::COMBINATION_TOTAL]) || !isset($QUERY[self::SPEED]) || !isset($QUERY[self::HASHCAT_STATE]) || !isset($QUERY[self::CRACKED_HASHES])){
      return false;
    }
    return true;
  }
  
  public const CHUNK_ID = "chunk";
  public const KEYSPACE_PROGRESS = "keyspaceProgress"; // aka curku
  public const COMBINATION_PROGRESS = "progress";
  public const COMBINATION_TOTAL = "total";
  public const SPEED = "speed";
  public const HASHCAT_STATE = "state";
  public const CRACKED_HASHES = "cracks";
}

class PQueryBenchmark extends PQuery {
  static function isValid($QUERY) {
    if(!isset($QUERY[self::TOKEN]) || !isset($QUERY[self::TASK_ID]) || !isset($QUERY[self::TYPE]) || !isset($QUERY[self::RESULT])){
      return false;
    }
    return true;
  }
  
  public const TASK_ID = "taskId";
  public const TYPE = "type";
  public const RESULT = "result";
}

class PQueryKeyspace extends PQuery {
  static function isValid($QUERY) {
    if(!isset($QUERY[self::TOKEN]) || !isset($QUERY[self::KEYSPACE]) || !isset($QUERY[self::TASK_ID])){
      return false;
    }
    return true;
  }
  
  public const KEYSPACE = "keyspace";
  public const TASK_ID = "taskId";
}

class PQueryChunk extends PQuery {
  static function isValid($QUERY) {
    if(!isset($QUERY[self::TOKEN]) || !isset($QUERY[self::TASK_ID])){
      return false;
    }
    return true;
  }
  
  public const TASK_ID = "taskId";
}

class PQueryTask extends PQuery {
  static function isValid($QUERY) {
    if(!isset($QUERY[self::TOKEN])){
      return false;
    }
    return true;
  }
}

class PQueryHashes extends PQuery {
  static function isValid($QUERY) {
    if(!isset($QUERY[self::TOKEN]) || !isset($QUERY[self::HASHLIST_ID])){
      return false;
    }
    return true;
  }
  
  public const HASHLIST_ID = "hashlist";
}

class PQueryFile extends PQuery {
  static function isValid($QUERY) {
    if(!isset($QUERY[self::TOKEN]) || !isset($QUERY[self::TASK_ID]) || !isset($QUERY[self::FILENAME])){
      return false;
    }
    return true;
  }

  public const TASK_ID = "task";
  public const FILENAME = "file";
}

class PQueryError extends PQuery {
  static function isValid($QUERY) {
    if(!isset($QUERY[self::TOKEN]) || !isset($QUERY[self::TASK_ID]) || !isset($QUERY[self::MESSAGE])){
      return false;
    }
    return true;
  }

  public const TASK_ID = "task";
  public const MESSAGE = "message";
}

class PQueryDownload extends PQuery {
  static function isValid($QUERY) {
    if(!isset($QUERY[self::TOKEN]) || !isset($QUERY[self::BINARY_TYPE])){
      return false;
    }
    return true;
  }
  
  public const BINARY_TYPE = "type";
  public const FORCE_UPDATE = "force"; // optional
}

class PQueryUpdate extends PQuery {
  static function isValid($QUERY) {
    if(!isset($QUERY[self::VERSION])){
      return false;
    }
    return true;
  }
  
  public const VERSION = "version";
}

class PQueryRegister extends PQuery {
  public static function isValid($QUERY){
    if(!isset($QUERY[self::VOUCHER]) || !isset($QUERY[self::GPUS]) || !isset($QUERY[self::USERID]) || !isset($QUERY[self::AGENT_NAME]) || !isset($QUERY[self::OPERATING_SYSTEM])){
      return false;
    }
    return true;
  }
  
  public const VOUCHER = "voucher";
  public const GPUS = "gpus";
  public const USERID = "uid";
  public const AGENT_NAME = "name";
  public const OPERATING_SYSTEM = "os";
}

######################
# Values definitions #
######################

abstract class PValues {
  public const SUCCESS = "SUCCESS";
  public const OK = "OK";
  public const NONE = "NONE";
  public const ERROR = "ERROR";
}

class PValuesDownloadBinaryType extends PValues {
  public const EXTRACTOR = "7zr";
  public const HASHCAT = "hashcat";
}

class PValuesBenchmarkType extends PValues {
  public const SPEED_TEST = "speed";
  public const RUN_TIME = "run";
}

class PValuesUpdateVersion extends PValues {
  public const UP_TO_DATE = "OK";
  public const NEW_VERSION = "NEW";
}

class PValuesDownloadVersion extends PValues {
  public const UP_TO_DATE = "OK";
  public const NEW_VERSION = "NEW";
}

class PValuesChunkType extends PValues {
  public const KEYSPACE_REQUIRED = "keyspace_required";
  public const BENCHMARK_REQUIRED = "benchmark";
  public const FULLY_DISPATCHED = "fully_dispatched";
}

########################
# Response definitions #
########################

abstract class PResponse {
  public const ACTION = "action";
  public const RESPONSE = "response";
}

class PResponseErrorMessage extends PResponse {
  public const MESSAGE = "message";
}

class PResponseRegister extends PResponse {
  public const TOKEN = "token";
}

class PResponseLogin extends PResponse {
  public const TIMEOUT = "timeout";
}

class PResponseUpdate extends PResponse {
  public const VERSION = "version";
  public const URL = "url";
}

class PResponseDownload extends PResponse {
  public const VERSION = "version";
  public const EXECUTABLE = "executable";
  public const URL = "url";
  public const FILES = "files";
  public const ROOT_DIR = "rootdir";
}

class PResponseError extends PResponse {
  // not additional values required
}

class PResponseFile extends PResponse {
  public const FILENAME = "filename";
  public const EXTENSION = "extension";
  public const URL = "url";
}

class PResponseTask extends PResponse {
  public const TASK_ID = "task";
  public const AGENT_WAIT = "wait";
  public const ATTACK_COMMAND = "attackcmd";
  public const CMD_PARAMETERS = "cmdpars";
  public const HASHLIST_ID = "hashlist";
  public const BENCHMARK = "bench";
  public const STATUS_TIMER = "statustimer";
  public const FILES = "files";
}

class PResponseChunk extends PResponse {
  public const CHUNK_ID = "chunk";
  public const KEYSPACE_SKIP = "skip";
  public const KEYSPACE_LENGTH = "length";
}

class PResponseKeyspace extends PResponse {
  public const KEYSPACE = "keyspace";
}

class PResponseBenchmark extends PResponse {
  public const BENCHMARK = "benchmark";
}

class PResponseSolve extends PResponse {
  public const NUM_CRACKED = "cracked";
  public const NUM_SKIPPED = "skipped";
  public const AGENT_COMMAND = "agent";
  public const HASH_ZAPS = "zaps";
}

######################
# Action definitions #
######################

class PActions {
  public const REGISTER = "register";
  public const LOGIN = "login";
  public const UPDATE = "update";
  public const DOWNLOAD = "download";
  public const ERROR = "error";
  public const FILE = "file";
  public const HASHES = "hashes";
  public const TASK = "task";
  public const CHUNK = "chunk";
  public const KEYSPACE = "keyspace";
  public const BENCHMARK = "bench";
  public const SOLVE = "solve";
}
