<?php

// user api defines (these are started with 'U')

#####################
# Query definitions #
#####################

abstract class UQuery { // include only generalized query values
  const SECTION = "section";
  const REQUEST = "request";
  const ACCESS_KEY  = "accessKey";

  /**
   * This function checks if all required values are given in the query
   *
   * @param $QUERY array the given query
   * @return bool true on valid, false if not
   */
  abstract static function isValid($QUERY);
}



######################
# Values definitions #
######################

abstract class UValues {
  const SUCCESS = "SUCCESS";
  const OK      = "OK";
  const NONE    = null;
  const ERROR   = "ERROR";
}



########################
# Response definitions #
########################

abstract class UResponse {
  const SECTION  = "section";
  const REQUEST  = "request";
  const RESPONSE = "response";
}

class UResponseErrorMessage extends UResponse {
  const MESSAGE = "message";
}



###############################
# Section/Request definitions #
###############################

class USection {
  const TEST = "test";
}

class UTestRequest {
  const CONNECTION = "connection";
}