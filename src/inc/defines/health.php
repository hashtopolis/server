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

class DHealthCheck {
  const NUM_HASHES = 100;
}

class DHealthCheckAction {
  const CREATE      = "create";
  const CREATE_PERM = DAccessControl::SERVER_CONFIG_ACCESS;
}

