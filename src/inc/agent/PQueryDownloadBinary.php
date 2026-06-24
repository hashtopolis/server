<?php

namespace Hashtopolis\inc\agent;

class PQueryDownloadBinary extends PQuery {
  static function isValid($QUERY) {
    if (!isset($QUERY[self::TOKEN]) || !isset($QUERY[self::BINARY_TYPE])) {
      return false;
    }
    return true;
  }
  
  const BINARY_TYPE       = "type";
  const BINARY_VERSION_ID = "binaryVersionId";
  const PREPROCESSOR_ID   = "preprocessorId";
}