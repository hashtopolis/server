<?php

namespace Hashtopolis\inc\agent;

class PQueryGetHealthCheck extends PQuery {
  static function isValid($QUERY) {
    if (!isset($QUERY[self::TOKEN])) {
      return false;
    }
    return true;
  }
}