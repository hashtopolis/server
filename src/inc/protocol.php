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
    if (!isset($QUERY[self::TOKEN]) || !isset($QUERY[self::CLIENT_SIGNATURE])) {
      return false;
    }
    return true;
  }
  
  const CLIENT_SIGNATURE = "clientSignature";
}

class PQueryGetFileStatus extends PQuery {
  static function isValid($QUERY) {
    if (!isset($QUERY[self::TOKEN])) {
      return false;
    }
    return true;
  }
}

class PQuerySendProgress extends PQuery {
  static function isValid($QUERY) {
    if (!isset($QUERY[self::TOKEN]) || !isset($QUERY[self::CHUNK_ID]) || !isset($QUERY[self::KEYSPACE_PROGRESS]) || !isset($QUERY[self::KEYSPACE_PROGRESS]) || !isset($QUERY[self::RELATIVE_PROGRESS]) || !isset($QUERY[self::SPEED]) || !isset($QUERY[self::HASHCAT_STATE]) || !isset($QUERY[self::CRACKED_HASHES])) {
      return false;
    }
    return true;
  }
  
  const CHUNK_ID          = "chunkId";
  const KEYSPACE_PROGRESS = "keyspaceProgress"; // aka curku
  const RELATIVE_PROGRESS = "relativeProgress";
  const SPEED             = "speed";
  const HASHCAT_STATE     = "state";
  const CRACKED_HASHES    = "cracks";
  
  // optional
  const DEBUG_OUTPUT = "debugOutput";
  const GPU_TEMP     = "gpuTemp";
  const GPU_UTIL     = "gpuUtil";
  const CPU_UTIL     = "cpuUtil";
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

class PQueryGetHealthCheck extends PQuery {
  static function isValid($QUERY) {
    if (!isset($QUERY[self::TOKEN])) {
      return false;
    }
    return true;
  }
}

class PQueryDeRegister extends PQuery {
  static function isValid($QUERY) {
    if (!isset($QUERY[self::TOKEN])) {
      return false;
    }
    return true;
  }
}

class PQuerySendHealthCheck extends PQuery {
  static function isValid($QUERY) {
    if (!isset($QUERY[self::TOKEN]) || !isset($QUERY[self::CHECK_ID]) || !isset($QUERY[self::NUM_CRACKED]) || !isset($QUERY[self::START]) || !isset($QUERY[self::END]) || !isset($QUERY[self::NUM_GPUS]) || !isset($QUERY[self::ERRORS])) {
      return false;
    }
    return true;
  }
  
  const CHECK_ID    = "checkId";
  const NUM_CRACKED = "numCracked";
  const START       = "start";
  const END         = "end";
  const NUM_GPUS    = "numGpus";
  const ERRORS      = "errors";
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

class PQueryGetFound extends PQuery {
  static function isValid($QUERY) {
    if (!isset($QUERY[self::TOKEN]) || !isset($QUERY[self::HASHLIST_ID])) {
      return false;
    }
    return true;
  }
  
  const HASHLIST_ID = "hashlistId";
}

class PQueryClientError extends PQuery {
  static function isValid($QUERY) {
    if (!isset($QUERY[self::TOKEN]) || !isset($QUERY[self::TASK_ID]) || !isset($QUERY[self::MESSAGE])) {
      return false;
    }
    return true;
  }
  
  const TASK_ID  = "taskId";
  const MESSAGE  = "message";
  const CHUNK_ID = "chunkId";
}

class PQueryDownloadBinary extends PQuery {
  static function isValid($QUERY) {
    if (!isset($QUERY[self::TOKEN]) || !isset($QUERY[self::BINARY_TYPE])) {
      return false;
    }
    return true;
  }
  
  const BINARY_TYPE       = "type";
  const BINARY_VERSION_ID = "binaryVersionId";
  const PREPROCESSOR_ID   = "preprocessorId";
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
  const CPU_ONLY   = "cpu-only";
}

######################
# Values definitions #
######################

abstract class PValues {
  const SUCCESS = "SUCCESS";
  const OK      = "OK";
  const NONE    = null;
  const ERROR   = "ERROR";
}

class PValuesTask extends PValues {
  const HEALTH_CHECK = -1;
}

class PValuesDownloadBinaryType extends PValues {
  const EXTRACTOR    = "7zr";
  const CRACKER      = "cracker";
  const PRINCE       = "prince";
  const UFTPD        = "uftpd";
  const PREPROCESSOR = "preprocessor";
}

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
  const HEALTH_CHECK       = "health_check";
  const OK                 = "OK";
}

########################
# Response definitions #
########################

abstract class PResponse {
  const ACTION   = "action";
  const RESPONSE = "response";
}

class PResponseGetFileStatus extends PResponse {
  const FILENAMES = "filenames";
}

class PResponseGetHealthCheck extends PResponse {
  const ATTACK            = "attack";
  const CRACKER_BINARY_ID = "crackerBinaryId";
  const HASHES            = "hashes";
  const CHECK_ID          = "checkId";
  const HASHLIST_ALIAS    = "hashlistAlias";
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
  const TIMEOUT   = "timeout";
  const MULTICAST = "multicastEnabled";
  const VERSION   = "server-version";
}

class PResponseClientUpdate extends PResponse {
  const VERSION = "version";
  const URL     = "url";
}

class PResponseBinaryDownload extends PResponse {
  const EXECUTABLE   = "executable";
  const URL          = "url";
  const NAME         = "name";
  const KEYSPACE_CMD = "keyspaceCommand";
  const SKIP_CMD     = "skipCommand";
  const LIMIT_CMD    = "limitCommand";
}

class PResponseError extends PResponse {
  // not additional values required
}

class PResponseSendHealthCheck extends PResponse {
  // not additional values required
}

class PResponseDeRegister extends PResponse {
  // not additional values required
}

class PResponseGetFile extends PResponse {
  const FILENAME  = "filename";
  const EXTENSION = "extension";
  const URL       = "url";
  const FILESIZE  = "filesize";
}

class PResponseGetTask extends PResponse {
  const TASK_ID              = "taskId";
  const CRACKER_ID           = "crackerId";
  const ATTACK_COMMAND       = "attackcmd";
  const CMD_PARAMETERS       = "cmdpars";
  const HASHLIST_ID          = "hashlistId";
  const BENCHMARK            = "bench";
  const STATUS_TIMER         = "statustimer";
  const FILES                = "files";
  const BENCHTYPE            = "benchType";
  const HASHLIST_ALIAS       = "hashlistAlias";
  const KEYSPACE             = "keyspace";
  const REASON               = "reason";
  const USE_PREPROCESSOR     = "usePreprocessor";
  const PREPROCESSOR         = "preprocessor";
  const PREPROCESSOR_COMMAND = "preprocessorCommand";
  const ENFORCE_PIPE         = "enforcePipe";
  const SLOW_HASH            = "slowHash";
  const USE_BRAIN            = "useBrain";
  const BRAIN_HOST           = "brainHost";
  const BRAIN_PORT           = "brainPort";
  const BRAIN_PASS           = "brainPass";
  const BRAIN_FEATURES       = "brainFeatures";
}

class PResponseGetChunk extends PResponse {
  const CHUNK_STATUS    = "status";
  const CHUNK_ID        = "chunkId";
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

class PResponseGetFound extends PResponse {
  const URL = "url";
}

######################
# Action definitions #
######################

class PActions {
  const REGISTER                  = "register";
  const LOGIN                     = "login";
  const UPDATE_CLIENT_INFORMATION = "updateInformation";
  const CHECK_CLIENT_VERSION      = "checkClientVersion";
  const DOWNLOAD_BINARY           = "downloadBinary";
  const CLIENT_ERROR              = "clientError";
  const GET_FILE                  = "getFile";
  const GET_HASHLIST              = "getHashlist";
  const GET_TASK                  = "getTask";
  const GET_CHUNK                 = "getChunk";
  const SEND_KEYSPACE             = "sendKeyspace";
  const SEND_BENCHMARK            = "sendBenchmark";
  const SEND_PROGRESS             = "sendProgress";
  const TEST_CONNECTION           = "testConnection";
  const GET_FILE_STATUS           = "getFileStatus";
  const GET_HEALTH_CHECK          = "getHealthCheck";
  const SEND_HEALTH_CHECK         = "sendHealthCheck";
  const GET_FOUND                 = "getFound";
  const DEREGISTER                = "deregister";
}
