<?php

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

Template::loadInstance("agents/vastai_search");
Menu::get()->setActive("vast_ai_agents_search");

//catch actions here...
if (isset($_POST['action']) && CSRF::check($_POST['csrf'])) {
  $agentHandler = new AgentHandler(@$_POST['agentId']);
  $agentHandler->handle($_POST['action']);
  if (UI::getNumMessages() == 0) {
    Util::refresh();
  }
}

$trueKeys = array('verified', 'datacenter');
$ltNumKeys = array('dph_total', 'dph');
$gtKeys = array('cpu_ram', 'gpu_ram', 'reliability2', 'disk_size', 'cpu_cores');
$eqKeys = array('gpu_name', 'cpu_name');
$vastaiApiKey = SConfig::getInstance()->getVal(Dconfig::VAST_AI_API_KEY);
UI::add('vastApiKey',$vastaiApiKey);

// maintain current search values / set default values
UI::add('search_gpu_name',get_or($_GET, 'gpu_name', ''));
UI::add('search_dph_total',$_GET['dph_total'] ?? '.4');
UI::add('search_gpu_ram',$_GET['gpu_ram'] ?? '');
UI::add('search_cpu_ram',$_GET['cpu_ram'] ?? '');
UI::add('search_disk_size',$_GET['disk_size'] ?? 8);
UI::add('search_cpu_cores',$_GET['cpu_cores'] ?? '');
UI::add('search_reliability2',$_GET['reliability2'] ?? '.8');

if (isset($_GET['datacenter']) === false || $_GET['datacenter'] === 'on'){
    // default setting
    UI::add('datacenterChecked',true);
} else {
    UI::add('datacenterChecked',false);
} 

if (isset($_GET['verified']) === false || $_GET['verified'] === 'on'){
    // default setting
    UI::add('verifiedChecked',true);
} else {
    UI::add('verifiedChecked',false);
} 

if (isset($_GET['uninterruptible']) === false || $_GET['uninterruptible'] === 'on'){
    // default setting
    UI::add('uninterruptibleChecked',true);
} else {
    UI::add('uninterruptibleChecked',false);
} 

UI::add('pageTitle', "Vast.AI Search");
$apiError = '';
if (isset($_GET['search']) && AccessControl::getInstance()->hasPermission(DAccessControl::MANAGE_AGENT_ACCESS)) {
  // prepare the query, I do not like this method of doing this, but it is working
  $query = array();
  $query = array_merge($query, array( "rentable" => array("eq" => true)));
  $query = array_merge($query, array( "rented" => array("eq" => false)));

  if(isset($_GET['uninterruptible']) == true && $_GET['uninterruptible'] == 'on'){
      $query = array_merge($query, array("type" => "ask"));
  } else {
      $query = array_merge($query, array("type" => "bid"));
  }

  foreach ($trueKeys as $key) {
    if (isset($_GET[$key]) === true && $_GET[$key] !== ''){
      if ($_GET[$key] == 'on'){
        $query = array_merge($query, array( $key => array("eq" => true)));
      }
    }
  }

  foreach ($eqKeys as $key) {
    if (isset($_GET[$key]) === true && $_GET[$key] !== ''){
      $query = array_merge($query, array( $key => array("eq" => $_GET[$key])));
    }
  }

  foreach ($ltNumKeys as $key) {
    if (isset($_GET[$key]) === true && $_GET[$key] !== ''){
      $query = array_merge($query, array( $key => array("lte" => $_GET[$key])));
    }
  }

  foreach ($gtKeys as $key) {
    if (isset($_GET[$key]) === true && $_GET[$key] !== ''){
      $query = array_merge($query, array( $key => array("gte" => $_GET[$key])));
    }
  }

  // make the query
  $response = searchGpus(json_encode($query));
  if (isset($response['error'])) {
      $apiError .= "vast.ai get offers error: " . $response;
  } else if (isset($response['offers']) == true) {
    $gpus = array();
    foreach ($response ['offers'] as $gpu) {
        $gpuClassInstance = new VastAiGPUArrayClass($gpu);
        array_push($gpus, $gpuClassInstance);
    }
  } else {
    $apiError  = 'missing offer key, response: ' . var_export($response , true);
  }
}

UI::add('apiError', $apiError);
UI::add('gpus', $gpus);
echo Template::getInstance()->render(UI::getObjects());
