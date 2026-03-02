<?php

namespace Hashtopolis\inc\agent;

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