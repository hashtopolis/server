<?php

class DLimits {
  const ACCESS_GROUP_MAX_LENGTH = 50;
}

class DZaps {
	const LAST_ZAP_CLEANING = "lastZapCleaning";
}

// log entry types
class DLogEntry {
  const WARN  = "warning";
  const ERROR = "error";
  const FATAL = "fatal error";
  const INFO  = "information";
}

class DLogEntryIssuer {
  const API  = "API";
  const USER = "User";
}

class DStats {
  const AGENTS_ONLINE      = "agentsOnline";
  const AGENTS_ACTIVE      = "agentsActive";
  const AGENTS_TOTAL_SPEED = "agentsTotalSpeed";
  const TASKS_TOTAL        = "tasksTotal";
  const TASKS_FINISHED     = "tasksFinished";
  const TASKS_RUNNING      = "tasksRunning";
  const TASKS_QUEUED       = "tasksQueued";

  const LAST_STAT_CLEANING = "lastStatCleaning";
}

class DPrince {
  const PRINCE_KEYSPACE = -1605;
}

// operating systems
class DOperatingSystem {
  const LINUX   = 0;
  const WINDOWS = 1;
  const OSX     = 2;
}

class DSearchAction {
  const SEARCH      = "search";
  const SEARCH_PERM = DAccessControl::VIEW_HASHLIST_ACCESS;
}