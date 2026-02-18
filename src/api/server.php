<?php

/**
 * This is the basic entry point for the agent API.
 * It should be entered as URL for the agent.
 *
 * The input is sent as JSON encoded data and the response will also be in JSON
 */

require_once(dirname(__FILE__) . "/../inc/startup/include.php");
set_time_limit(0);

header("Content-Type: application/json");
$QUERY = json_decode(file_get_contents('php://input'), true);

$api = null;
switch ($QUERY[PQuery::ACTION]) {
  /**
   * Used to test the connection between the client and the server
   */
  case PActions::TEST_CONNECTION:
    $api = new APITestConnection();
    break;
  /**
   * Registers a new agent to the server.
   */
  case PActions::REGISTER:
    $api = new APIRegisterAgent();
    break;
  /**
   * The client sends it hardware specs.
   */
  case PActions::UPDATE_CLIENT_INFORMATION:
    $api = new APIUpdateClientInformation();
    break;
  /**
   * A client logs in. Basically not much happens here, it's mainly to validate the token.
   */
  case PActions::LOGIN:
    $api = new APILogin();
    break;
  /**
   * The client asks if a new client binary is available.
   */
  case PActions::CHECK_CLIENT_VERSION:
    $api = new APICheckClientVersion();
    break;
  /**
   * The client requests a download for hashcat binary, 7z binary or similar.
   */
  case PActions::DOWNLOAD_BINARY:
    $api = new APIDownloadBinary();
    break;
  /**
   * An error occurred on the client and he sends the error information to the server
   */
  case PActions::CLIENT_ERROR:
    $api = new APIClientError();
    break;
  /**
   * The client wants to download a file he needs for executing a task
   */
  case PActions::GET_FILE:
    $api = new APIGetFile();
    break;
  /**
   * The client wants to download a hashlist for his task
   */
  case PActions::GET_HASHLIST:
    $api = new APIGetHashlist();
    break;
  /**
   * The client requests to get a task he should work on
   */
  case PActions::GET_TASK:
    $api = new APIGetTask();
    break;
  /**
   * The client requests a chunk on the task he is assigned
   */
  case PActions::GET_CHUNK:
    $api = new APIGetChunk();
    break;
  /**
   * The client measured the keyspace for a task and sends the resulting number
   */
  case PActions::SEND_KEYSPACE:
    $api = new APISendKeyspace();
    break;
  /**
   * The client did a benchmark on his assigned task and sends the benchmark result
   */
  case PActions::SEND_BENCHMARK:
    $api = new APISendBenchmark();
    break;
  /**
   * The client is currently working and he sends an update about the progress, cracked hashes and gets zapped hashes
   */
  case PActions::SEND_PROGRESS:
    $api = new APISendProgress();
    break;
  /**
   * The client requests a list of filenames which are deleted on the server to check if he should clean up some files
   */
  case PActions::GET_FILE_STATUS:
    $api = new APIGetFileStatus();
    break;
  /**
   * Retrieve a health check (if available)
   */
  case PActions::GET_HEALTH_CHECK:
    $api = new APIGetHealthCheck();
    break;
  /**
   * Send the results of a health check
   */
  case PActions::SEND_HEALTH_CHECK:
    $api = new APISendHealthCheck();
    break;
  /**
   * Retrieve the found hashes of a hashlist
   */
  case PActions::GET_FOUND:
    $api = new APIGetFound();
    break;
  /**
   * De-register a client on exit
   */
  case PActions::DEREGISTER:
    $api = new APIDeRegisterAgent();
    break;
}

DServerLog::log(DServerLog::TRACE, "Received from " . Util::getIP() . ": " . json_encode($QUERY));

if ($api == null) {
  $api = new APITestConnection();
  $api->sendErrorResponse("INV", "Invalid query!");
}
else {
  $api->execute($QUERY);
}

