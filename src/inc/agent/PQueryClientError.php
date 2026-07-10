<?php

namespace Hashtopolis\inc\agent;

class PQueryClientError extends PQuery {
  static function isValid(array $QUERY): bool {
    if (!isset($QUERY[self::TOKEN]) || !isset($QUERY[self::TASK_ID]) || !isset($QUERY[self::MESSAGE])) {
      return false;
    }
    return true;
  }
  
  const TASK_ID  = "taskId";
  const MESSAGE  = "message";
  const CHUNK_ID = "chunkId";
}