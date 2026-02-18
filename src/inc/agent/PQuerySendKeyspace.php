<?php

namespace Hashtopolis\inc\agent;

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