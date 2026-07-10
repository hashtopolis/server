<?php

namespace Hashtopolis\inc\agent;

class PQueryGetHashlist extends PQuery {
  static function isValid(array $QUERY): bool {
    if (!isset($QUERY[self::TOKEN]) || !isset($QUERY[self::HASHLIST_ID])) {
      return false;
    }
    return true;
  }
  
  const HASHLIST_ID = "hashlistId";
}