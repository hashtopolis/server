<?php

namespace Hashtopolis\inc\agent;

class PQueryDeRegister extends PQuery {
  static function isValid(array $QUERY): bool {
    if (!isset($QUERY[self::TOKEN])) {
      return false;
    }
    return true;
  }
}