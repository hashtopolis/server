<?php
require_once(dirname(__FILE__) . "/../inc/load.php");
set_time_limit(0);

$QUERY = json_decode(@$_POST[PQueryValues::QUERY], true);
header("Content-Type: application/json");

//debug logging
//TODO: remove later
file_put_contents("../query.log", Util::getIP() . "=" . $_POST[PQueryValues::QUERY] . "\n", FILE_APPEND);

switch ($QUERY[PQueryValues::ACTION]) {
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
      API::sendErrorResponse('download', "Invalid token!");
    }
    API::downloadApp($QUERY);
    break;
  case PActions::ERROR:
    if (API::checkToken($QUERY)) {
      API::sendErrorResponse('error', "Invalid token!");
    }
    API::agentError($QUERY);
    break;
  case PActions::FILE:
    if (API::checkToken($QUERY)) {
      API::sendErrorResponse('file', "Invalid token!");
    }
    API::getFile($QUERY);
    break;
  case PActions::HASHES:
    if (API::checkToken($QUERY)) {
      API::sendErrorResponse('hashes', "Invalid token!");
    }
    API::getHashes($QUERY);
    break;
  case PActions::TASK:
    if (API::checkToken($QUERY)) {
      API::sendErrorResponse('task', "Invalid token!");
    }
    API::getTask($QUERY);
    break;
  case PActions::CHUNK:
    if (API::checkToken($QUERY)) {
      API::sendErrorResponse('task', "Invalid token!");
    }
    API::getChunk($QUERY);
    break;
  case PActions::KEYSPACE:
    if (API::checkToken($QUERY)) {
      API::sendErrorResponse('task', "Invalid token!");
    }
    API::setKeyspace($QUERY);
    break;
  case PActions::BENCHMARK:
    if (API::checkToken($QUERY)) {
      API::sendErrorResponse('bench', "Invalid token!");
    }
    API::setBenchmark($QUERY);
    break;
  case PActions::SOLVE:
    if (API::checkToken($QUERY)) {
      API::sendErrorResponse('solve', "Invalid token!");
    }
    API::solve($QUERY);
    break;
  default:
    API::sendErrorResponse("INV", "Invalid query!");
}

