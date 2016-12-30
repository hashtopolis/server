<?php
require_once(dirname(__FILE__) . "/../inc/load.php");
set_time_limit(0);

$QUERY = json_decode(@$_POST['query'], true);
header("Content-Type: application/json");

//debug logging
//TODO: remove later
file_put_contents("../query.log", Util::getIP() . "=" . $_POST['query'] . "\n", FILE_APPEND);

switch ($QUERY['action']) {
    case "register":
        API::registerAgent($QUERY);
        break;
    case "login":
        API::loginAgent($QUERY);
        break;
    case "update":
        API::checkClientUpdate($QUERY);
        break;
    case "download":
        if (API::checkToken($QUERY)) {
            API::sendErrorResponse('download', "Invalid token!");
        }
        API::downloadApp($QUERY);
        break;
    case 'error':
        if (API::checkToken($QUERY)) {
            API::sendErrorResponse('error', "Invalid token!");
        }
        API::agentError($QUERY);
        break;
    case 'file':
        if (API::checkToken($QUERY)) {
            API::sendErrorResponse('file', "Invalid token!");
        }
        API::getFile($QUERY);
        break;
    case "hashes":
        if (API::checkToken($QUERY)) {
            API::sendErrorResponse('hashes', "Invalid token!");
        }
        API::getHashes($QUERY);
        break;
    case "task":
        if (API::checkToken($QUERY)) {
            API::sendErrorResponse('task', "Invalid token!");
        }
        API::getTask($QUERY);
        break;
    case "chunk":
        if (API::checkToken($QUERY)) {
            API::sendErrorResponse('task', "Invalid token!");
        }
        API::getChunk($QUERY);
        break;
    case "keyspace":
        if (API::checkToken($QUERY)) {
            API::sendErrorResponse('task', "Invalid token!");
        }
        API::setKeyspace($QUERY);
        break;
    case "bench":
        if (API::checkToken($QUERY)) {
            API::sendErrorResponse('bench', "Invalid token!");
        }
        API::setBenchmark($QUERY);
        break;
    case "solve":
        if (API::checkToken($QUERY)) {
            API::sendErrorResponse('solve', "Invalid token!");
        }
        API::solve($QUERY);
        break;
    default:
        API::sendErrorResponse("INV", "Invalid query!");
}

