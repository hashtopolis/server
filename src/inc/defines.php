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
  public const INIT = 0;
  public const AUTOTUNE = 1;
  public const RUNNING = 2;
  public const PAUSED = 3;
  public const EXHAUSTED = 4;
  public const CRACKED = 5;
  public const ABORTED = 6;
  public const QUIT = 7;
  public const BYPASS = 8;
  public const ABORTED_CHECKPOINT = 9;
  public const STATUS_ABORTED_RUNTIME = 10;
}

class DOperatingSystem {
  public const LINUX = 0;
  public const WINDOWS = 1;
  public const OSX = 2;
}

class DHashlistFormat {
  public const PLAIN = 0;
  public const WPA = 1;
  public const BINARY = 2;
  public const SUPERHASHLIST = 3;
}

class DAccessLevel { // if you change any of them here, you need to check if this is consistent with the database
  public const VIEW_ONLY = 1;
  public const READ_ONLY = 5;
  public const USER = 20;
  public const SUPERUSER = 30;
  public const ADMINISTRATOR = 50;
}

// protocol defines (these are started with 'P')
class PQueryValues { // include only generalized query values
  public const QUERY = "query";
  public const ACTION = "action";
}

class PActions {
  public const REGISTER = "register";
  public const LOGIN = "login";
  public const UPDATE = "update";
  public const DOWNLOAD = "download";
  public const ERROR = "error";
  public const FILE = "file";
  public const HASHES = "hashes";
  public const TASK = "task";
  public const CHUNK = "chunk";
  public const KEYSPACE = "keyspace";
  public const BENCHMARK = "bench";
  public const SOLVE = "solve";
}

