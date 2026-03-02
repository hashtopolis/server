<?php

namespace Hashtopolis\inc\defines;

class DHealthCheckAction {
  const CREATE      = "create";
  const CREATE_PERM = DAccessControl::SERVER_CONFIG_ACCESS;
  
  const RESET_AGENT      = "resetAgent";
  const RESET_AGENT_PERM = DAccessControl::SERVER_CONFIG_ACCESS;
  
  const DELETE_HEALTH_CHECK      = "deleteHealthCheck";
  const DELETE_HEALTH_CHECK_PERM = DAccessControl::SERVER_CONFIG_ACCESS;
}