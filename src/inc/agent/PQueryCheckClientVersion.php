<?php

namespace Hashtopolis\inc\agent;

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