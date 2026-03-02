<?php

namespace Hashtopolis\inc\agent;

class PQueryLogin extends PQuery {
  static function isValid($QUERY) {
    if (!isset($QUERY[self::TOKEN]) || !isset($QUERY[self::CLIENT_SIGNATURE])) {
      return false;
    }
    return true;
  }
  
  const CLIENT_SIGNATURE = "clientSignature";
}