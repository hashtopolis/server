<?php

use DBA\Factory;
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

require_once(dirname(__FILE__) . "/inc/load.php");
require_once(dirname(__FILE__) . "/inc/vastAiUtils.php");

if (!Login::getInstance()->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}

AccessControl::getInstance()->checkPermission(DViewControl::AGENTS_VIEW_PERM);

//catch actions here...
if (isset($_POST['action']) && CSRF::check($_POST['csrf'])) {
  $agentHandler = new AgentHandler(@$_POST['agentId']);
  $agentHandler->handle($_POST['action']);
  if (UI::getNumMessages() == 0) {
    Util::refresh();
  }
}

Template::loadInstance("agents/vastai_rent");

$apiError = '';
$infoMessage = '';
$rentError = '';
$vastaiApiKey = SConfig::getInstance()->getVal(Dconfig::VAST_AI_API_KEY);
$rentSuccess = false;
UI::add('vastApiKey', $vastaiApiKey);

if (isset($_POST['rent']) && AccessControl::getInstance()->hasPermission(DAccessControl::CREATE_AGENT_ACCESS)) {
  UI::add('rentType',$_POST['rent']);
  foreach(array_keys($_POST) as $postKey){
    if (strpos($postKey, 'vastid-') !== 0) {
        continue;
    }

    $id = substr($postKey, strlen('vastid-'));
    $query = array("id" => array("eq" => $id ));

    // PULL the details for the provided id
    $response = searchGpus(json_encode($query));
    if (isset($response['error'])) {
        $apiError .= "vast.ai get offers error: " . $response . "<br>";
    } else if (isset($response['offers']) == true) {
      if (sizeof($response['offers']) == 1){
        $gpu = new VastAiGPUArrayClass($response['offers'][0]);
        UI::add('gpu', $gpu);
      } else if (sizeof($response['offers']) > 1){
        $apiError .= 'results were ambiguous for provided machine id<br>';
      } else {
        $apiError .= 'no results found for provided machine id<br>';
      }
    } else {
      $apiError  = 'missing offer key, response: ' . var_export($response , true) . '<br>';
    }

    // RENT LOGIC
    $imageUrl = SConfig::getInstance()->getVal(Dconfig::VAST_IMAGE_URL);
    $baseUrl = SConfig::getInstance()->getVal(Dconfig::VAST_HASHTOPOLIS_BASE_URL);
    $vastaiImageLogin = SConfig::getInstance()->getVal(Dconfig::VAST_IMAGE_LOGIN);

    if ($baseUrl === '') {
      echo 'No base url string was set. set it <a href="/config.php?view=8">here</a><br>';
      die();
    }
    if ($imageUrl === '') {
      echo 'No image url string was set. set it <a href="/config.php?view=8">here</a><br>';
      die();
    }
    if (isset($_POST['disk']) === false || $_POST['disk'] === '') {
      echo 'A disk value must be set.<br>';
      die();
    }
    if (isset($_POST['price']) === false || $_POST['price'] === ''){
      $price = null;
    }

    $disk  = $_POST['disk'];
    $price = $_POST['price'];
    // this additional substring gets referenced to properly remove
    // voucher and agent artifacts by the destroy function.
    // it is also intuitive when checking agents/vouchers.
    $voucher = makeVastaiVoucher($id);

    $response = rentMachine($vastaiApiKey, $id, $imageUrl, $price, $disk, $voucher, $baseUrl, $vastaiImageLogin);
    if (isset($response['error'])) {
        $rentError .= "vast.ai error: " . $response['msg'];
    } else if (isset($response['success'])) {
        $infoMessage .= '<br>Successfully rented: ' . $id;
        $rentSuccess = true;
    } else {
        $rentError .= "vast.ai error: " . $response . '<br>';
    }

  }

  UI::add('posted', true);
  UI::add('rentError', $rentError);
  UI::add('infoMessage', $infoMessage);
  UI::add('rentSuccess', $rentSuccess);
} else if (isset($_GET['rent']) && AccessControl::getInstance()->hasPermission(DAccessControl::CREATE_AGENT_ACCESS)) {
  // VIEWING DETAILS
  $id = $_GET['rent'];
  $query = array("id" => array("eq" => $id ));
  $response = searchGpus(json_encode($query));
  if (is_array($response) === false){
      $apiError .= $response . "\n";
  }
  if (isset($response['offers']) == true) {
    if (sizeof($response['offers']) == 1){
      $gpu = new VastAiGPUArrayClass($response['offers'][0]);
      UI::add('gpu', $gpu);
    } else if (sizeof($response['offers']) > 1){
      $apiError .= 'results were ambiguous for provided machine id' . "\n";
    } else {
      $apiError .= 'no results found for provided machine id' . "\n";
    }
  } else {
      $apiError .= 'missing offer key, response: ' . var_export($response, true) . "\n";
  }
} else {
    echo "Error: No rent param provided";
    die();
}

UI::add('apiError', $apiError);
echo Template::getInstance()->render(UI::getObjects());
