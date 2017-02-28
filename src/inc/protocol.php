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
  const QUERY = "query";
  const ACTION = "action";
  const TOKEN = "token";
  
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
  
  const CHUNK_ID = "chunk";
  const KEYSPACE_PROGRESS = "keyspaceProgress"; // aka curku
  const COMBINATION_PROGRESS = "progress";
  const COMBINATION_TOTAL = "total";
  const SPEED = "speed";
  const HASHCAT_STATE = "state";
  const CRACKED_HASHES = "cracks";
}

class PQueryBenchmark extends PQuery {
  static function isValid($QUERY) {
    if(!isset($QUERY[self::TOKEN]) || !isset($QUERY[self::TASK_ID]) || !isset($QUERY[self::TYPE]) || !isset($QUERY[self::RESULT])){
      return false;
    }
    return true;
  }
  
  const TASK_ID = "taskId";
  const TYPE = "type";
  const RESULT = "result";
}

class PQueryKeyspace extends PQuery {
  static function isValid($QUERY) {
    if(!isset($QUERY[self::TOKEN]) || !isset($QUERY[self::KEYSPACE]) || !isset($QUERY[self::TASK_ID])){
      return false;
    }
    return true;
  }
  
  const KEYSPACE = "keyspace";
  const TASK_ID = "taskId";
}

class PQueryChunk extends PQuery {
  static function isValid($QUERY) {
    if(!isset($QUERY[self::TOKEN]) || !isset($QUERY[self::TASK_ID])){
      return false;
    }
    return true;
  }
  
  const TASK_ID = "taskId";
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
  
  const HASHLIST_ID = "hashlist";
}

class PQueryFile extends PQuery {
  static function isValid($QUERY) {
    if(!isset($QUERY[self::TOKEN]) || !isset($QUERY[self::TASK_ID]) || !isset($QUERY[self::FILENAME])){
      return false;
    }
    return true;
  }

  const TASK_ID = "task";
  const FILENAME = "file";
}

class PQueryError extends PQuery {
  static function isValid($QUERY) {
    if(!isset($QUERY[self::TOKEN]) || !isset($QUERY[self::TASK_ID]) || !isset($QUERY[self::MESSAGE])){
      return false;
    }
    return true;
  }

  const TASK_ID = "task";
  const MESSAGE = "message";
}

class PQueryDownload extends PQuery {
  static function isValid($QUERY) {
    if(!isset($QUERY[self::TOKEN]) || !isset($QUERY[self::BINARY_TYPE])){
      return false;
    }
    return true;
  }
  
  const BINARY_TYPE = "type";
  const FORCE_UPDATE = "force"; // optional
}

class PQueryUpdate extends PQuery {
  static function isValid($QUERY) {
    if(!isset($QUERY[self::VERSION]) || !isset($QUERY[self::TYPE])){
      return false;
    }
    return true;
  }
  
  const VERSION = "version";
  const TYPE = "type";
}

class PQueryRegister extends PQuery {
  public static function isValid($QUERY){
    if(!isset($QUERY[self::VOUCHER]) || !isset($QUERY[self::GPUS]) || !isset($QUERY[self::USERID]) || !isset($QUERY[self::AGENT_NAME]) || !isset($QUERY[self::OPERATING_SYSTEM])){
      return false;
    }
    return true;
  }
  
  const VOUCHER = "voucher";
  const GPUS = "gpus";
  const USERID = "uid";
  const AGENT_NAME = "name";
  const OPERATING_SYSTEM = "os";
}

######################
# Values definitions #
######################

abstract class PValues {
  const SUCCESS = "SUCCESS";
  const OK = "OK";
  const NONE = "NONE";
  const ERROR = "ERROR";
}

class PValuesDownloadBinaryType extends PValues {
  const EXTRACTOR = "7zr";
  const HASHCAT = "hashcat";
}

class PValuesBenchmarkType extends PValues {
  const SPEED_TEST = "speed";
  const RUN_TIME = "run";
}

class PValuesUpdateVersion extends PValues {
  const UP_TO_DATE = "OK";
  const NEW_VERSION = "NEW";
}

class PValuesDownloadVersion extends PValues {
  const UP_TO_DATE = "OK";
  const NEW_VERSION = "NEW";
}

class PValuesChunkType extends PValues {
  const KEYSPACE_REQUIRED = "keyspace_required";
  const BENCHMARK_REQUIRED = "benchmark";
  const FULLY_DISPATCHED = "fully_dispatched";
  const OK = "OK";
}

########################
# Response definitions #
########################

abstract class PResponse {
  const ACTION = "action";
  const RESPONSE = "response";
}

class PResponseErrorMessage extends PResponse {
  const MESSAGE = "message";
}

class PResponseRegister extends PResponse {
  const TOKEN = "token";
}

class PResponseLogin extends PResponse {
  const TIMEOUT = "timeout";
}

class PResponseUpdate extends PResponse {
  const VERSION = "version";
  const URL = "url";
}

class PResponseDownload extends PResponse {
  const VERSION = "version";
  const EXECUTABLE = "executable";
  const URL = "url";
  const ROOT_DIR = "rootdir";
}

class PResponseError extends PResponse {
  // not additional values required
}

class PResponseFile extends PResponse {
  const FILENAME = "filename";
  const EXTENSION = "extension";
  const URL = "url";
}

class PResponseTask extends PResponse {
  const TASK_ID = "task";
  const ATTACK_COMMAND = "attackcmd";
  const CMD_PARAMETERS = "cmdpars";
  const HASHLIST_ID = "hashlist";
  const BENCHMARK = "bench";
  const STATUS_TIMER = "statustimer";
  const FILES = "files";
  const BENCHTYPE = "benchType";
  const HASHLIST_ALIAS = "hashlistAlias";
}

class PResponseChunk extends PResponse {
  const CHUNK_STATUS = "status";
  const CHUNK_ID = "chunk";
  const KEYSPACE_SKIP = "skip";
  const KEYSPACE_LENGTH = "length";
}

class PResponseKeyspace extends PResponse {
  const KEYSPACE = "keyspace";
}

class PResponseBenchmark extends PResponse {
  const BENCHMARK = "benchmark";
}

class PResponseSolve extends PResponse {
  const NUM_CRACKED = "cracked";
  const NUM_SKIPPED = "skipped";
  const AGENT_COMMAND = "agent";
  const HASH_ZAPS = "zaps";
}

######################
# Action definitions #
######################

class PActions {
  const REGISTER = "register";
  const LOGIN = "login";
  const UPDATE = "update";
  const DOWNLOAD = "download";
  const ERROR = "error";
  const FILE = "file";
  const HASHES = "hashes";
  const TASK = "task";
  const CHUNK = "chunk";
  const KEYSPACE = "keyspace";
  const BENCHMARK = "bench";
  const SOLVE = "solve";
  const TEST = "test";
}
