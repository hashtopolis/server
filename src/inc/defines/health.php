<?php

class DHealthCheckAgentStatus {
  const PENDING   = 0;
  const COMPLETED = 1;
  const FAILED    = -1;
}

class DHealthCheckStatus {
  const PENDING   = 0;
  const COMPLETED = 1;
  const ABORTED   = -1;
}

class DHealthCheckType {
  const BRUTE_FORCE = 0;
}

class DHealthCheckMode {
  const MD5    = 0;
  const BCRYPT = 3200;
}

class DHealthCheck {
  const NUM_HASHES = 100;
}

class DHealthCheckAction {
  const CREATE      = "create";
  const CREATE_PERM = DAccessControl::SERVER_CONFIG_ACCESS;
  
  const RESET_AGENT      = "resetAgent";
  const RESET_AGENT_PERM = DAccessControl::SERVER_CONFIG_ACCESS;
  
  const DELETE_HEALTH_CHECK      = "deleteHealthCheck";
  const DELETE_HEALTH_CHECK_PERM = DAccessControl::SERVER_CONFIG_ACCESS;
}

