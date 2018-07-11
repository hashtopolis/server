<?php

/**
 * This is the basic entry point for the User API.
 * It should be entered as endpoint for any tool using the API.
 *
 * The input is sent as JSON encoded data and the response will also be in JSON
 */

require_once(dirname(__FILE__) . "/../inc/load.php");
set_time_limit(0);

header("Content-Type: application/json");
$QUERY = json_decode(file_get_contents('php://input'), true);

$api = null;
switch ($QUERY['section']) {
  
}

if ($api == null) {
  // TODO: response that the query was invalid
}
else {
  $api->execute($QUERY);
}

