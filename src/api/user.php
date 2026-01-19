<?php

/**
 * This is the basic entry point for the User API.
 * It should be entered as endpoint for any tool using the API.
 *
 * The input is sent as JSON encoded data and the response will also be in JSON
 */

require_once(dirname(__FILE__) . "/../inc/startup/include.php");
set_time_limit(0);

header("Content-Type: application/json");
$QUERY = json_decode(file_get_contents('php://input'), true);

$api = null;
switch ($QUERY[UQuery::SECTION]) {
  case USection::TEST:
    $api = new UserAPITest();
    break;
  case USection::ACCESS:
    $api = new UserAPIAccess();
    break;
  case USection::AGENT:
    $api = new UserAPIAgent();
    break;
  case USection::CONFIG:
    $api = new UserAPIConfig();
    break;
  case USection::CRACKER:
    $api = new UserAPICracker();
    break;
  case USection::FILE:
    $api = new UserAPIFile();
    break;
  case USection::GROUP:
    $api = new UserAPIGroup();
    break;
  case USection::HASHLIST:
    $api = new UserAPIHashlist();
    break;
  case USection::PRETASK:
    $api = new UserAPIPretask();
    break;
  case USection::SUPERHASHLIST:
    $api = new UserAPISuperhashlist();
    break;
  case USection::SUPERTASK:
    $api = new UserAPISupertask();
    break;
  case USection::TASK:
    $api = new UserAPITask();
    break;
  case USection::USER:
    $api = new UserAPIUser();
    break;
  case USection::ACCOUNT:
    $api = new UserAPIAccount();
    break;
}

DServerLog::log(DServerLog::TRACE, "Received from " . Util::getIP() . ": " . json_encode($QUERY));

if ($api == null) {
  $api = new UserAPITest();
  $api->sendErrorResponse("INV", "INV", "Invalid user api query!");
}
else {
  if ($QUERY[UQuery::SECTION] != USection::TEST) {
    $api->checkApiKey($QUERY[UQuery::SECTION], $QUERY[UQuery::REQUEST], $QUERY);
  }
  $api->execute($QUERY);
}

