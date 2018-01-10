<?php

// protocol defines (these are started with 'P')

#####################
# Query definitions #
#####################

abstract class PQuery { // include only generalized query values
  const QUERY  = "query";
  const ACTION = "action";
  const TOKEN  = "token";
  
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
    if (!isset($QUERY[self::TOKEN])) {
      return false;
    }
    return true;
  }
}

class PQuerySendProgress extends PQuery {
  static function isValid($QUERY) {
    if (!isset($QUERY[self::TOKEN]) || !isset($QUERY[self::CHUNK_ID]) || !isset($QUERY[self::KEYSPACE_PROGRESS]) || !isset($QUERY[self::COMBINATION_PROGRESS]) || !isset($QUERY[self::COMBINATION_TOTAL]) || !isset($QUERY[self::SPEED]) || !isset($QUERY[self::HASHCAT_STATE]) || !isset($QUERY[self::CRACKED_HASHES])) {
      return false;
    }
    return true;
  }
  
  // TODO: update sendProgress query
  const CHUNK_ID             = "chunk";
  const KEYSPACE_PROGRESS    = "keyspaceProgress"; // aka curku
  const COMBINATION_PROGRESS = "progress";
  const COMBINATION_TOTAL    = "total";
  const SPEED                = "speed";
  const HASHCAT_STATE        = "state";
  const CRACKED_HASHES       = "cracks";
}

class PQuerySendBenchmark extends PQuery {
  static function isValid($QUERY) {
    if (!isset($QUERY[self::TOKEN]) || !isset($QUERY[self::TASK_ID]) || !isset($QUERY[self::TYPE]) || !isset($QUERY[self::RESULT])) {
      return false;
    }
    return true;
  }
  
  const TASK_ID = "taskId";
  const TYPE    = "type";
  const RESULT  = "result";
}

class PQuerySendKeyspace extends PQuery {
  static function isValid($QUERY) {
    if (!isset($QUERY[self::TOKEN]) || !isset($QUERY[self::KEYSPACE]) || !isset($QUERY[self::TASK_ID])) {
      return false;
    }
    return true;
  }
  
  const KEYSPACE = "keyspace";
  const TASK_ID  = "taskId";
}

class PQueryGetChunk extends PQuery {
  static function isValid($QUERY) {
    if (!isset($QUERY[self::TOKEN]) || !isset($QUERY[self::TASK_ID])) {
      return false;
    }
    return true;
  }
  
  const TASK_ID = "taskId";
}

class PQueryGetTask extends PQuery {
  static function isValid($QUERY) {
    if (!isset($QUERY[self::TOKEN])) {
      return false;
    }
    return true;
  }
}

class PQueryGetHashlist extends PQuery {
  static function isValid($QUERY) {
    if (!isset($QUERY[self::TOKEN]) || !isset($QUERY[self::HASHLIST_ID])) {
      return false;
    }
    return true;
  }
  
  const HASHLIST_ID = "hashlistId";
}

class PQueryGetFile extends PQuery {
  static function isValid($QUERY) {
    if (!isset($QUERY[self::TOKEN]) || !isset($QUERY[self::TASK_ID]) || !isset($QUERY[self::FILENAME])) {
      return false;
    }
    return true;
  }
  
  const TASK_ID  = "taskId";
  const FILENAME = "file";
}

class PQueryClientError extends PQuery {
  static function isValid($QUERY) {
    if (!isset($QUERY[self::TOKEN]) || !isset($QUERY[self::TASK_ID]) || !isset($QUERY[self::MESSAGE])) {
      return false;
    }
    return true;
  }
  
  const TASK_ID = "taskId";
  const MESSAGE = "message";
}

class PQueryDownloadBinary extends PQuery {
  static function isValid($QUERY) {
    if (!isset($QUERY[self::TOKEN]) || !isset($QUERY[self::BINARY_TYPE])) {
      return false;
    }
    return true;
  }
  
  const BINARY_TYPE  = "type";
  const FORCE_UPDATE = "force"; // optional
}

class PQueryCheckClientVersion extends PQuery {
  static function isValid($QUERY) {
    if (!isset($QUERY[self::VERSION]) || !isset($QUERY[self::TYPE])) {
      return false;
    }
    return true;
  }
  
  const VERSION = "version";
  const TYPE    = "type";
}

class PQueryUpdateInformation extends PQuery {
  public static function isValid($QUERY) {
    if (!isset($QUERY[self::TOKEN]) || !isset($QUERY[self::DEVICES]) || !isset($QUERY[self::UID]) || !isset($QUERY[self::OPERATING_SYSTEM])) {
      return false;
    }
    return true;
  }
  
  const DEVICES          = "devices";
  const UID              = "uid";
  const OPERATING_SYSTEM = "os";
}

class PQueryRegister extends PQuery {
  public static function isValid($QUERY) {
    if (!isset($QUERY[self::VOUCHER]) || !isset($QUERY[self::AGENT_NAME])) {
      return false;
    }
    return true;
  }
  
  const VOUCHER    = "voucher";
  const AGENT_NAME = "name";
}

######################
# Values definitions #
######################

abstract class PValues {
  const SUCCESS = "SUCCESS";
  const OK      = "OK";
  const NONE    = "NONE";
  const ERROR   = "ERROR";
}

// TODO: this must be in db for the cracker types
class PValuesDownloadBinaryType extends PValues {
  const EXTRACTOR = "7zr";
  const HASHCAT   = "hashcat";
}

// TODO: this must be modeled otherwise to reflect other benchmark types for new crackers
class PValuesBenchmarkType extends PValues {
  const SPEED_TEST = "speed";
  const RUN_TIME   = "run";
}

class PValuesUpdateVersion extends PValues {
  const UP_TO_DATE  = "OK";
  const NEW_VERSION = "NEW";
}

class PValuesDownloadVersion extends PValues {
  const UP_TO_DATE  = "OK";
  const NEW_VERSION = "NEW";
}

class PValuesChunkType extends PValues {
  const KEYSPACE_REQUIRED  = "keyspace_required";
  const BENCHMARK_REQUIRED = "benchmark";
  const FULLY_DISPATCHED   = "fully_dispatched";
  const CRACKER_UPDATE     = "cracker_update";
  const OK                 = "OK";
}

########################
# Response definitions #
########################

abstract class PResponse {
  const ACTION   = "action";
  const RESPONSE = "response";
}

class PResponseErrorMessage extends PResponse {
  const MESSAGE = "message";
}

class PResponseRegister extends PResponse {
  const TOKEN = "token";
}

class PResponseGetHashlist extends PResponse {
  const URL = "url";
}

class PResponseLogin extends PResponse {
  const TIMEOUT = "timeout";
}

class PResponseClientUpdate extends PResponse {
  const VERSION = "version";
  const URL     = "url";
}

class PResponseBinaryDownload extends PResponse {
  const VERSION    = "version";
  const EXECUTABLE = "executable";
  const URL        = "url";
  const ROOT_DIR   = "rootdir";
}

class PResponseError extends PResponse {
  // not additional values required
}

class PResponseGetFile extends PResponse {
  const FILENAME  = "filename";
  const EXTENSION = "extension";
  const URL       = "url";
}

class PResponseGetTask extends PResponse {
  const TASK_ID        = "task";
  const ATTACK_COMMAND = "attackcmd";
  const CMD_PARAMETERS = "cmdpars";
  const HASHLIST_ID    = "hashlist";
  const BENCHMARK      = "bench";
  const STATUS_TIMER   = "statustimer";
  const FILES          = "files";
  const BENCHTYPE      = "benchType";
  const HASHLIST_ALIAS = "hashlistAlias";
}

class PResponseGetChunk extends PResponse {
  const CHUNK_STATUS    = "status";
  const CHUNK_ID        = "chunk";
  const KEYSPACE_SKIP   = "skip";
  const KEYSPACE_LENGTH = "length";
}

class PResponseSendKeyspace extends PResponse {
  const KEYSPACE = "keyspace";
}

class PResponseSendBenchmark extends PResponse {
  const BENCHMARK = "benchmark";
}

class PResponseSendProgress extends PResponse {
  const NUM_CRACKED   = "cracked";
  const NUM_SKIPPED   = "skipped";
  const AGENT_COMMAND = "agent";
  const HASH_ZAPS     = "zaps";
}

######################
# Action definitions #
######################

class PActions {
  const REGISTER                  = "register";
  const LOGIN                     = "login";
  const UPDATE_CLIENT_INFORMATION = "updateInformation";
  const CHECK_CLIENT_VERSION      = "checkClientVersion";
  const DOWNLOAD_BINARy           = "downloadBinary";
  const CLIENT_ERROR              = "clientError";
  const GET_FILE                  = "getFile";
  const GET_HASHLIST              = "getHashlist";
  const GET_TASK                  = "getTask";
  const GET_CHUNK                 = "getChunk";
  const SEND_KEYSPACE             = "sendKeyspace";
  const SEND_BENCHMARK            = "sendBenchmark";
  const SEND_PROGRESS             = "sendProgress";
  const TEST_CONNECTION           = "testConnection";
}
