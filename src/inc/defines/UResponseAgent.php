<?php

namespace Hashtopolis\inc\defines;

class UResponseAgent extends UResponse {
  const VOUCHER  = "voucher";
  const VOUCHERS = "vouchers";
  
  const BINARIES          = "binaries";
  const BINARIES_NAME     = "name";
  const BINARIES_URL      = "url";
  const BINARIES_VERSION  = "version";
  const BINARIES_OS       = "os";
  const BINARIES_FILENAME = "filename";
  const AGENT_URL         = "apiUrl";
  
  const AGENTS         = "agents";
  const AGENTS_ID      = "agentId";
  const AGENTS_NAME    = "name";
  const AGENTS_DEVICES = "devices";
  
  const AGENT_NAME            = "name";
  const AGENT_DEVICES         = "devices";
  const AGENT_OWNER           = "owner";
  const AGENT_OWNER_ID        = "userId";
  const AGENT_OWNER_NAME      = "username";
  const AGENT_CPU_ONLY        = "isCpuOnly";
  const AGENT_TRUSTED         = "isTrusted";
  const AGENT_ACTIVE          = "isActive";
  const AGENT_TOKEN           = "token";
  const AGENT_PARAMS          = "extraParameters";
  const AGENT_ERRORS          = "errorFlag";
  const AGENT_ACTIVITY        = "lastActivity";
  const AGENT_ACTIVITY_ACTION = "action";
  const AGENT_ACTIVITY_TIME   = "time";
  const AGENT_ACTIVITY_IP     = "ip";
}