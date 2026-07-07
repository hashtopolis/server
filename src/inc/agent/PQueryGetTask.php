<?php

namespace Hashtopolis\inc\agent;

class PQueryGetTask extends PQuery {
  static function isValid(array $QUERY): bool {
    if (!isset($QUERY[self::TOKEN])) {
      return false;
    }
    return true;
  }
}