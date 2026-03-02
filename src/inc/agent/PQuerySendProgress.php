<?php

namespace Hashtopolis\inc\agent;

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