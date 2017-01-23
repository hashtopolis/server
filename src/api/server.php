<?php
require_once(dirname(__FILE__) . "/../inc/load.php");
set_time_limit(0);

$QUERY = json_decode(@$_POST[PQuery::QUERY], true);
header("Content-Type: application/json");

//debug logging
//TODO: remove later
file_put_contents("../query.log", Util::getIP() . "=" . $_POST[PQuery::QUERY] . "\n", FILE_APPEND);

switch ($QUERY[PQuery::ACTION]) {
  case PActions::REGISTER:
    API::registerAgent($QUERY);
    break;
  case PActions::LOGIN:
    API::loginAgent($QUERY);
    break;
  case PActions::UPDATE:
    API::checkClientUpdate($QUERY);
    break;
  case PActions::DOWNLOAD:
    if (API::checkToken($QUERY)) {
      API::sendErrorResponse(PActions::DOWNLOAD, "Invalid token!");
    }
    API::downloadApp($QUERY);
    break;
  case PActions::ERROR:
    if (API::checkToken($QUERY)) {
      API::sendErrorResponse(PActions::ERROR, "Invalid token!");
    }
    API::agentError($QUERY);
    break;
  case PActions::FILE:
    if (API::checkToken($QUERY)) {
      API::sendErrorResponse(PActions::FILE, "Invalid token!");
    }
    API::getFile($QUERY);
    break;
  case PActions::HASHES:
    if (API::checkToken($QUERY)) {
      API::sendErrorResponse(PActions::HASHES, "Invalid token!");
    }
    API::getHashes($QUERY);
    break;
  case PActions::TASK:
    if (API::checkToken($QUERY)) {
      API::sendErrorResponse(PActions::TASK, "Invalid token!");
    }
    API::getTask($QUERY);
    break;
  case PActions::CHUNK:
    if (API::checkToken($QUERY)) {
      API::sendErrorResponse(PActions::CHUNK, "Invalid token!");
    }
    API::getChunk($QUERY);
    break;
  case PActions::KEYSPACE:
    if (API::checkToken($QUERY)) {
      API::sendErrorResponse(PActions::KEYSPACE, "Invalid token!");
    }
    API::setKeyspace($QUERY);
    break;
  case PActions::BENCHMARK:
    if (API::checkToken($QUERY)) {
      API::sendErrorResponse(PActions::BENCHMARK, "Invalid token!");
    }
    API::setBenchmark($QUERY);
    break;
  case PActions::SOLVE:
    if (API::checkToken($QUERY)) {
      API::sendErrorResponse(PActions::SOLVE, "Invalid token!");
    }
    API::solve($QUERY);
    break;
  default:
    API::sendErrorResponse("INV", "Invalid query!");
}

