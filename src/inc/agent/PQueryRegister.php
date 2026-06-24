<?php

namespace Hashtopolis\inc\agent;

class PQueryRegister extends PQuery {
  public static function isValid($QUERY) {
    if (!isset($QUERY[self::VOUCHER]) || !isset($QUERY[self::AGENT_NAME])) {
      return false;
    }
    return true;
  }
  
  const VOUCHER    = "voucher";
  const AGENT_NAME = "name";
  const CPU_ONLY   = "cpu-only";
}