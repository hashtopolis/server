<?php
require_once(dirname(__FILE__) . "/../inc/load.php");
set_time_limit(0);

header("Content-Type: application/json");
if(!isset($_POST[PQuery::QUERY])){
  API::sendErrorResponse("ERROR", "Query value is not set!");
}
$QUERY = json_decode(@$_POST[PQuery::QUERY], true);


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
    API::checkToken(PActions::DOWNLOAD, $QUERY);
    API::downloadApp($QUERY);
    break;
  case PActions::ERROR:
    API::checkToken(PActions::ERROR, $QUERY);
    API::agentError($QUERY);
    break;
  case PActions::FILE:
    API::checkToken(PActions::FILE, $QUERY);
    API::getFile($QUERY);
    break;
  case PActions::HASHES:
    API::checkToken(PActions::HASHES, $QUERY);
    API::getHashes($QUERY);
    break;
  case PActions::TASK:
    API::checkToken(PActions::TASK, $QUERY);
    API::getTask($QUERY);
    break;
  case PActions::CHUNK:
    API::checkToken(PActions::CHUNK, $QUERY);
    API::getChunk($QUERY);
    break;
  case PActions::KEYSPACE:
    API::checkToken(PActions::KEYSPACE, $QUERY);
    API::setKeyspace($QUERY);
    break;
  case PActions::BENCHMARK:
    API::checkToken(PActions::BENCHMARK, $QUERY);
    API::setBenchmark($QUERY);
    break;
  case PActions::SOLVE:
    API::checkToken(PActions::SOLVE, $QUERY);
    API::solve($QUERY);
    break;
  default:
    API::sendErrorResponse("INV", "Invalid query!");
}

