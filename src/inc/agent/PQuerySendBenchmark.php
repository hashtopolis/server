<?php

namespace Hashtopolis\inc\agent;

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