<?php

namespace Hashtopolis\inc\agent;

class PQueryUpdateInformation extends PQuery {
  public static function isValid($QUERY) {
    if (!isset($QUERY[self::TOKEN]) || !isset($QUERY[self::DEVICES]) || !isset($QUERY[self::UID]) || !isset($QUERY[self::OPERATING_SYSTEM])) {
      return false;
    }
    return true;
  }
  
  const DEVICES          = "devices";
  const UID              = "uid";
  const OPERATING_SYSTEM = "os";
}