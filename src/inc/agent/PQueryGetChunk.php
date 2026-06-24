<?php

namespace Hashtopolis\inc\agent;

class PQueryGetChunk extends PQuery {
  static function isValid($QUERY) {
    if (!isset($QUERY[self::TOKEN]) || !isset($QUERY[self::TASK_ID])) {
      return false;
    }
    return true;
  }
  
  const TASK_ID = "taskId";
}