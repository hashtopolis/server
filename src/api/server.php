<?php

/**
 * This is the basic entry point for the client API.
 * It should be entered as URL for the client.
 *
 * The input is sent as JSON encoded data and the response will also be in JSON (with some exceptions).
 */

require_once(dirname(__FILE__) . "/../inc/load.php");
set_time_limit(0);

header("Content-Type: application/json");
$QUERY = json_decode(file_get_contents('php://input'), true);

switch ($QUERY[PQuery::ACTION]) {
  /**
   * Used to test the connection between the client and the server
   */
  case PActions::TEST:
    API::test();
    break;
  /**
   * Registers a new agent to the server. The client sends all the required informations about the agent.
   */
  case PActions::REGISTER:
    API::registerAgent($QUERY);
    break;
  /**
   * A client logs in. Basically not much happens here, it's mainly to validate the token.
   */
  case PActions::LOGIN:
    API::loginAgent($QUERY);
    break;
  /**
   * The client asks if a new client binary is available.
   */
  case PActions::UPDATE:
    API::checkClientUpdate($QUERY);
    break;
  /**
   * The client requests a download for hashcat binary, 7z binary or similar.
   */
  case PActions::DOWNLOAD:
    API::checkToken(PActions::DOWNLOAD, $QUERY);
    API::downloadApp($QUERY);
    break;
  /**
   * An error occured on the client and he sends the error information to the server
   */
  case PActions::ERROR:
    API::checkToken(PActions::ERROR, $QUERY);
    API::agentError($QUERY);
    break;
  /**
   * The client wants to download a file he needs for executing a task
   */
  case PActions::FILE:
    API::checkToken(PActions::FILE, $QUERY);
    API::getFile($QUERY);
    break;
  /**
   * The client wants to download a hashlist for his task
   */
  case PActions::HASHES:
    API::checkToken(PActions::HASHES, $QUERY);
    API::getHashes($QUERY);
    break;
  /**
   * The client requests to get a task he should work on
   */
  case PActions::TASK:
    API::checkToken(PActions::TASK, $QUERY);
    API::getTask($QUERY);
    break;
  /**
   * The client requests a chunk on the task he is assigned
   */
  case PActions::CHUNK:
    API::checkToken(PActions::CHUNK, $QUERY);
    API::getChunk($QUERY);
    break;
  /**
   * The client measured the keyspace for a task and sends the resulting number
   */
  case PActions::KEYSPACE:
    API::checkToken(PActions::KEYSPACE, $QUERY);
    API::setKeyspace($QUERY);
    break;
  /**
   * The client did a benchmark on his assigned task and sends the benchmark result
   */
  case PActions::BENCHMARK:
    API::checkToken(PActions::BENCHMARK, $QUERY);
    API::setBenchmark($QUERY);
    break;
  /**
   * The client is currently working and he sends an update about the progress, cracked hashes and gets zapped hashes
   */
  case PActions::SOLVE:
    API::checkToken(PActions::SOLVE, $QUERY);
    API::solve($QUERY);
    break;
  default:
    API::sendErrorResponse("INV", "Invalid query!");
}

