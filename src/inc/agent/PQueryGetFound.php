<?php

namespace Hashtopolis\inc\agent;

class PQueryGetFound extends PQuery {
  static function isValid($QUERY) {
    if (!isset($QUERY[self::TOKEN]) || !isset($QUERY[self::HASHLIST_ID])) {
      return false;
    }
    return true;
  }
  
  const HASHLIST_ID = "hashlistId";
}