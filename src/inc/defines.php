<?php
/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 23.01.17
 * Time: 19:02
 */

/*
 * All define classes should start with 'D'
 */

// hashcat status numbers
class DHashcatStatus{
  const INIT = 0;
  const AUTOTUNE = 1;
  const RUNNING = 2;
  const PAUSED = 3;
  const EXHAUSTED = 4;
  const CRACKED = 5;
  const ABORTED = 6;
  const QUIT = 7;
  const BYPASS = 8;
  const ABORTED_CHECKPOINT = 9;
  const STATUS_ABORTED_RUNTIME = 10;
}

// operating systems
class DOperatingSystem {
  const LINUX = 0;
  const WINDOWS = 1;
  const OSX = 2;
}

// hashlist formats
class DHashlistFormat {
  const PLAIN = 0;
  const WPA = 1;
  const BINARY = 2;
  const SUPERHASHLIST = 3;
}

// access levels for user groups
class DAccessLevel { // if you change any of them here, you need to check if this is consistent with the database
  const VIEW_ONLY = 1;
  const READ_ONLY = 5;
  const USER = 20;
  const SUPERUSER = 30;
  const ADMINISTRATOR = 50;
}

// used config values
class DConfig {
  const BENCHMARK_TIME = "benchtime";
  const CHUNK_DURATION = "chunktime";
  const CHUNK_TIMEOUT = "chunktimeout";
  const AGENT_TIMEOUT = "agenttimeout";
  const HASHES_PAGE_SIZE = "pagingSize";
  const FIELD_SEPARATOR = "fieldseparator";
  const HASHLIST_ALIAS = "hashlistAlias";
  const STATUS_TIMER = "statustimer";
}


