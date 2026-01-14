<?php

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

require_once(dirname(__FILE__) . "/inc/startup/load.php");

if (isset($_GET['download'])) {
  $agentHandler = new AgentHandler();
  try {
    $agentHandler->downloadAgent($_GET['download']);
  }
  catch (HTException $e) {
    die($e->getMessage());
  }
}

if (!Login::getInstance()->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}

AccessControl::getInstance()->checkPermission(DViewControl::AGENTS_VIEW_PERM);

Template::loadInstance("agents/index");
Menu::get()->setActive("agents_list");

//catch actions here...
if (isset($_POST['action']) && CSRF::check($_POST['csrf'])) {
  $agentHandler = new AgentHandler(@$_POST['agentId']);
  $agentHandler->handle($_POST['action']);
  if (UI::getNumMessages() == 0) {
    Util::refresh();
  }
}

// load groups for user
$qF = new QueryFilter(AccessGroupUser::USER_ID, Login::getInstance()->getUserID(), "=");
$userGroups = Factory::getAccessGroupUserFactory()->filter([Factory::FILTER => $qF]);
$accessGroupIds = array();
foreach ($userGroups as $userGroup) {
  $accessGroupIds[] = $userGroup->getAccessGroupId();
}

if (isset($_GET['id'])) {
  //show agent detail
  Template::loadInstance("agents/detail");
  $agent = Factory::getAgentFactory()->get($_GET['id']);
  if (!$agent) {
    UI::printError("ERROR", "Agent not found!");
  }
  else if (!AccessUtils::userCanAccessAgent($agent, Login::getInstance()->getUser())) {
    UI::printError("ERROR", "No access to this agent!");
  }
  else {
    // uniq devices lines and prepend with count
    $tmp_devices_tuple = array_count_values(explode("\n", $agent->getDevices()));
    $devices_tuple = array();
    foreach ($tmp_devices_tuple as $key => $value) {
      $devices_tuple[] = str_replace("*", "&nbsp;&nbsp", sprintf("%'*2d&times ", $value) . $key);
    }
    $agent->setDevices(implode("\n", $devices_tuple));

    UI::add('agent', $agent);
    UI::add('users', Factory::getUserFactory()->filter([]));
    UI::add('pageTitle', "Agent details for " . $agent->getAgentName());
    
    // load all tasks which are valid for this agent
    UI::add('allTasks', TaskUtils::getBestTask($agent, true));
    
    $qF = new QueryFilter(AccessGroupAgent::AGENT_ID, $agent->getId(), "=", Factory::getAccessGroupAgentFactory());
    $jF = new JoinFilter(Factory::getAccessGroupAgentFactory(), AccessGroup::ACCESS_GROUP_ID, AccessGroupAgent::ACCESS_GROUP_ID);
    $joined = Factory::getAccessGroupFactory()->filter([Factory::FILTER => $qF, Factory::JOIN => $jF]);
    UI::add('accessGroups', $joined[Factory::getAccessGroupFactory()->getModelName()]);
    
    // load agent detail data
    $data = AgentUtils::getGraphData($agent, [DAgentStatsType::GPU_TEMP]);
    UI::add('deviceTemp', json_encode($data['sets']));
    UI::add('deviceTempAvailable', (sizeof($data['sets']) > 0) ? true : false);
    UI::add('deviceTempXLabels', json_encode($data['xlabels']));
    UI::add('deviceTempAxes', json_encode($data['axes']));
    
    $data = AgentUtils::getGraphData($agent, [DAgentStatsType::GPU_UTIL]);
    UI::add('deviceUtil', json_encode($data['sets']));
    UI::add('deviceUtilAvailable', (sizeof($data['sets']) > 0) ? true : false);
    UI::add('deviceUtilXLabels', json_encode($data['xlabels']));
    UI::add('deviceUtilAxes', json_encode($data['axes']));

    $data = AgentUtils::getGraphData($agent, [DAgentStatsType::CPU_UTIL]);
    UI::add('cpuUtil', json_encode($data['sets']));
    UI::add('cpuUtilAvailable', (sizeof($data['sets']) > 0) ? true : false);
    UI::add('cpuUtilXLabels', json_encode($data['xlabels']));
    UI::add('cpuUtilAxes', json_encode($data['axes']));

    $qF = new QueryFilter(Assignment::AGENT_ID, $agent->getId(), "=");
    $assignment = Factory::getAssignmentFactory()->filter([Factory::FILTER => $qF], true);
    $currentTask = 0;
    if ($assignment != null) {
      $currentTask = $assignment->getTaskId();
    }
    UI::add('currentTask', $currentTask);
    
    $qF = new QueryFilter(AgentError::AGENT_ID, $agent->getId(), "=");
    UI::add('errors', Factory::getAgentErrorFactory()->filter([Factory::FILTER => $qF]));
    
    $qF = new QueryFilter(Chunk::AGENT_ID, $agent->getId(), "=");
    $oF = new OrderFilter(Chunk::DISPATCH_TIME, "DESC");
    $chunks = Factory::getChunkFactory()->filter([Factory::FILTER => $qF, Factory::ORDER => $oF]);
    $timeSpent = 0;
    foreach ($chunks as $chunk) {
      $timeSpent += max($chunk->getSolveTime(), $chunk->getDispatchTime()) - $chunk->getDispatchTime();
    }
    $chunks = array_slice($chunks, 0, 50);
    UI::add('chunks', $chunks);
    UI::add('timeSpent', $timeSpent);
  }
}
else if (isset($_GET['new']) && AccessControl::getInstance()->hasPermission(DAccessControl::CREATE_AGENT_ACCESS)) {
  Menu::get()->setActive("agents_new");
  Template::loadInstance("agents/new");
  UI::add('pageTitle', "New Agent");
  UI::add('vouchers', Factory::getRegVoucherFactory()->filter([]));
  UI::add('agentBinaries', Factory::getAgentBinaryFactory()->filter([]));
  
  $url = explode("/", $_SERVER['PHP_SELF']);
  unset($url[sizeof($url) - 1]);
  UI::add('apiUrl', Util::buildServerUrl() . implode("/", $url) . "/api/server.php");
  UI::add('agentUrl', Util::buildServerUrl() . implode("/", $url) . "/agents.php?download=");
}
else {
  UI::add('pageTitle', "Agents");
  
  // load all agents which are in an access group the user has access to
  $qF = new ContainFilter(AccessGroupAgent::ACCESS_GROUP_ID, $accessGroupIds);
  $accessGroupAgents = Factory::getAccessGroupAgentFactory()->filter([Factory::FILTER => $qF]);
  $agentIds = array();
  foreach ($accessGroupAgents as $accessGroupAgent) {
    $agentIds[] = $accessGroupAgent->getAgentId();
  }
  
  $oF = new OrderFilter(Agent::AGENT_ID, "ASC", Factory::getAgentFactory());
  $qF = new ContainFilter(Agent::AGENT_ID, $agentIds);
  $agents = Factory::getAgentFactory()->filter([Factory::FILTER => $qF, Factory::ORDER => $oF]);
  $accessGroupAgents = new DataSet();
  foreach ($agents as $agent) {
    $qF = new QueryFilter(AccessGroupAgent::AGENT_ID, $agent->getId(), "=", Factory::getAccessGroupAgentFactory());
    $jF = new JoinFilter(Factory::getAccessGroupAgentFactory(), AccessGroup::ACCESS_GROUP_ID, AccessGroupAgent::ACCESS_GROUP_ID);
    $joined = Factory::getAccessGroupFactory()->filter([Factory::FILTER => $qF, Factory::JOIN => $jF]);
    $accessGroupAgents->addValue($agent->getId(), $joined[Factory::getAccessGroupFactory()->getModelName()]);
    // uniq devices lines and prepend with count
    $tmp_devices_tuple = array_count_values(explode("\n", $agent->getDevices()));
    $devices_tuple = array();
    foreach ($tmp_devices_tuple as $key => $value) {
      $devices_tuple[] = str_replace("*", "&nbsp;&nbsp", sprintf("%'*2d&times ", $value) . $key);
    }
    $agent->setDevices(implode("\n", $devices_tuple));
  }
  
  UI::add('accessGroupAgents', $accessGroupAgents);
  UI::add('agents', $agents);
  UI::add('numAgents', sizeof($agents));
}

echo Template::getInstance()->render(UI::getObjects());




