<?php

// user api defines (these are started with 'U')

#####################
# Query definitions #
#####################

abstract class UQuery { // include only generalized query values
  const SECTION = "section";
  const REQUEST = "request";
  const ACCESS_KEY  = "accessKey";
}

class UQueryAgent extends UQuery {
  const VOUCHER = "voucher";
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

class UValuesAccess extends UValues {
  const NOT_FOUND = "notFound";
  const EXPIRED   = "expired";
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

class UResponseAgent extends UResponse {
  const VOUCHER = "voucher";

  const BINARIES          = "binaries";
  const BINARIES_NAME     = "name";
  const BINARIES_URL      = "url";
  const BINARIES_VERSION  = "version";
  const BINARIES_OS       = "os";
  const BINARIES_FILENAME = "filename";
  const AGENT_URL         = "apiUrl";
}

###############################
# Section/Request definitions #
###############################

class USection {
  const TEST          = "test";
  const AGENT         = "agent";
  const TASK          = "task";
  const PRETASK       = "pretask";
  const SUPERTASK     = "supertask";
  const HASHLIST      = "hashlist";
  const SUPERHASHLIST = "superhashlist";
  const FILE          = "file";
  const CRACKER       = "cracker";
  const CONFIG        = "config";
  const USER          = "user";
  const GROUP         = "group";
  const ACCESS        = "access";
}

class USectionTest {
  const CONNECTION = "connection";
  const ACCESS     = "access";
}

class USectionAgent {
  const CREATE_VOUCHER = "createVoucher";
  const GET_BINARIES   = "getBinaries";
  const LIST_VOUCHERS  = "listVouchers";
  const DELETE_VOUCHER = "deleteVoucher";
  const LIST_AGENTS    = "listAgents";
}

class USectionTask {}

class USectionPretask {}

class USectionSupertask {}

class USectionHashlist {}

class USectionSuperhashlist {}

class USectionFile {}

class USectionCracker {}

class USectionConfig {}

class USectionUser {}

class USectionGroup {}

class USectionAccess {}