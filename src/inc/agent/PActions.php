<?php

namespace Hashtopolis\inc\agent;

class PActions {
  const REGISTER                  = "register";
  const LOGIN                     = "login";
  const UPDATE_CLIENT_INFORMATION = "updateInformation";
  const CHECK_CLIENT_VERSION      = "checkClientVersion";
  const DOWNLOAD_BINARY           = "downloadBinary";
  const CLIENT_ERROR              = "clientError";
  const GET_FILE                  = "getFile";
  const GET_HASHLIST              = "getHashlist";
  const GET_TASK                  = "getTask";
  const GET_CHUNK                 = "getChunk";
  const SEND_KEYSPACE             = "sendKeyspace";
  const SEND_BENCHMARK            = "sendBenchmark";
  const SEND_PROGRESS             = "sendProgress";
  const TEST_CONNECTION           = "testConnection";
  const GET_FILE_STATUS           = "getFileStatus";
  const GET_HEALTH_CHECK          = "getHealthCheck";
  const SEND_HEALTH_CHECK         = "sendHealthCheck";
  const GET_FOUND                 = "getFound";
  const DEREGISTER                = "deregister";
}