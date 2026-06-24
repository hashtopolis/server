<?php

namespace Hashtopolis\inc\agent;

class PQuerySendHealthCheck extends PQuery {
  static function isValid($QUERY) {
    if (!isset($QUERY[self::TOKEN]) || !isset($QUERY[self::CHECK_ID]) || !isset($QUERY[self::NUM_CRACKED]) || !isset($QUERY[self::START]) || !isset($QUERY[self::END]) || !isset($QUERY[self::NUM_GPUS]) || !isset($QUERY[self::ERRORS])) {
      return false;
    }
    return true;
  }
  
  const CHECK_ID    = "checkId";
  const NUM_CRACKED = "numCracked";
  const START       = "start";
  const END         = "end";
  const NUM_GPUS    = "numGpus";
  const ERRORS      = "errors";
}