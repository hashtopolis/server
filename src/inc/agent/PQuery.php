<?php

namespace Hashtopolis\inc\agent;

abstract class PQuery { // include only generalized query values
  const QUERY  = "query";
  const ACTION = "action";
  const TOKEN  = "token";
  
  /**
   * This function checks if all required values are given in the query
   *
   * @param $QUERY array the given query
   * @return bool true on valid, false if not
   */
  abstract static function isValid($QUERY);
}
