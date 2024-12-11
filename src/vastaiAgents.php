<?php

use DBA\RegVoucher;
use DBA\Config;
use DBA\AccessGroup;
use DBA\AccessGroupAgent;
use DBA\AccessGroupUser;
use DBA\Agent;
use DBA\AgentError;
use DBA\Assignment;
use DBA\Chunk;
use DBA\ContainFilter;
use DBA\JoinFilter;
use DBA\OrderFilter;
use DBA\QueryFilter;
use DBA\Factory;

require_once(dirname(__FILE__) . "/inc/load.php");
require_once(dirname(__FILE__) . "/inc/vastAiUtils.php");

if (!Login::getInstance()->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}

AccessControl::getInstance()->checkPermission(DViewControl::AGENTS_VIEW_PERM);

Template::loadInstance("agents/vastai");
Menu::get()->setActive("vast_ai_agents");

//catch actions here...
if (isset($_POST['action']) && CSRF::check($_POST['csrf'])) {
  $agentHandler = new AgentHandler(@$_POST['agentId']);
  $agentHandler->handle($_POST['action']);
  if (UI::getNumMessages() == 0) {
    Util::refresh();
  }
}

$vastaiApiKey = SConfig::getInstance()->getVal(Dconfig::VAST_AI_API_KEY);
$apiError = '';
$infoMessage = '';

UI::add('vastApiKey',$vastaiApiKey);
UI::add('pageTitle', "Vast.AI Agents");

// DESTROY LOGIC
if (isset($_POST['destroy']) && AccessControl::getInstance()->hasPermission(DAccessControl::MANAGE_AGENT_ACCESS)) {
  if ($_POST['destroy'] === '' ){
    echo "No machine id provided for the destroy action";
    die();
  }
  $machineId = $_POST['destroy'];

  $response = vastAiDestroyInstance($vastaiApiKey, $machineId);
  if (isset($response['error'])) {
      $apiError .= "vast.ai get instances error: " . $response;
  } else if (isset($response['success'])) {
      $infoMessage = 'Successfully destroyed instance: ' . $machineId;
  } else {
      $apiError .= "vast.ai get instances error: " . $response;
  }

  // REMOVE VOUCHER FOR DESTROYED INSTANCE IF IT HASNT BEEN USED YET
  if (isset($_POST['startingVoucher']) && $_POST['startingVoucher'] != '') {
    $qF = new QueryFilter(RegVoucher::VOUCHER, $_POST['startingVoucher'], "=");
    $check = Factory::getRegVoucherFactory()->filter([Factory::FILTER => $qF]);
    if ( $check != null ) {
      AgentUtils::deleteVoucher($_POST['startingVoucher']);
    }
  }
} 

// INSTANCE LIST LOGIC to list the current vast.ai instances associated with the api key
$response = getVastAiAgents($vastaiApiKey);
if (isset($response['error'])) {
    $apiError .= "vast.ai get instances error: " . $response;
} else if (isset($response['instances']) == true) {
  $gpus = array();
  foreach ($response['instances'] as $gpu) {
      $gpuClassInstance = new VastAiGPUArrayClass($gpu);
      array_push($gpus, $gpuClassInstance);
  }
} else {
    $apiError .= 'missing instances, response: ' . var_export($response, true);
}

// TEST IF AUTO-RELOAD IS ENABLED
$autorefresh = 0;
if (isset($_COOKIE['autorefresh']) && $_COOKIE['autorefresh'] == '1') {
  $autorefresh = 10;
}
if (isset($_POST['toggleautorefresh'])) {
  if ($autorefresh != 0) {
    $autorefresh = 0;
    setcookie("autorefresh", "0", time() - 600);
  }
  else {
    $autorefresh = 10;
    setcookie("autorefresh", "1", time() + 3600 * 24);
  }
  Util::refresh();
}
if ($autorefresh > 0) { //renew cookie
  setcookie("autorefresh", "1", time() + 3600 * 24);
}

UI::add('autorefresh', 0);
if (isset($_GET['id']) || !isset($_GET['new'])) {
  UI::add('autorefresh', $autorefresh);
  UI::add('autorefreshUrl', "");
}

UI::add('infoMessage', $infoMessage);
UI::add('apiError', $apiError);
UI::add('gpus', $gpus);
echo Template::getInstance()->render(UI::getObjects());
