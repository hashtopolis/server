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

require_once(dirname(__FILE__) . "/inc/load.php");

/** @var Login $LOGIN */
/** @var array $OBJECTS */

if (isset($_GET['download'])) {
  $agentHandler = new AgentHandler();
  try {
    $agentHandler->downloadAgent($_GET['download']);
  }
  catch (HTException $e) {
    die($e->getMessage());
  }
}

if (!$LOGIN->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}

$ACCESS_CONTROL->checkPermission(DViewControl::AGENTS_VIEW_PERM);

$TEMPLATE = new Template("agents/index");
$MENU->setActive("agents_list");

//catch actions here...
if (isset($_POST['action']) && CSRF::check($_POST['csrf'])) {
  $agentHandler = new AgentHandler(@$_POST['agentId']);
  $agentHandler->handle($_POST['action']);
  if (UI::getNumMessages() == 0) {
    Util::refresh();
  }
}

// load groups for user
$qF = new QueryFilter(AccessGroupUser::USER_ID, $LOGIN->getUserID(), "=");
$userGroups = Factory::getAccessGroupUserFactory()->filter([Factory::FILTER => $qF]);
$accessGroupIds = array();
foreach ($userGroups as $userGroup) {
  $accessGroupIds[] = $userGroup->getAccessGroupId();
}

if (isset($_GET['id'])) {
  //show agent detail
  $TEMPLATE = new Template("agents/detail");
  $agent = Factory::getAgentFactory()->get($_GET['id']);
  if (!$agent) {
    UI::printError("ERROR", "Agent not found!");
  }
  else if (!AccessUtils::userCanAccessAgent($agent, $LOGIN->getUser())) {
    UI::printError("ERROR", "No access to this agent!");
  }
  else {
    $OBJECTS['agent'] = $agent;
    $OBJECTS['users'] = Factory::getUserFactory()->filter([]);
    $OBJECTS['pageTitle'] .= "Agent details for " . $agent->getAgentName();

    // load all tasks which are valid for this agent
    $OBJECTS['allTasks'] = TaskUtils::getBestTask($agent, true);

    $qF = new QueryFilter(AccessGroupAgent::AGENT_ID, $agent->getId(), "=", Factory::getAccessGroupAgentFactory());
    $jF = new JoinFilter(Factory::getAccessGroupAgentFactory(), AccessGroup::ACCESS_GROUP_ID, AccessGroupAgent::ACCESS_GROUP_ID);
    $joined = Factory::getAccessGroupFactory()->filter([Factory::FILTER => $qF, Factory::JOIN => $jF]);
    $OBJECTS['accessGroups'] = $joined[Factory::getAccessGroupFactory()->getModelName()];

    // load agent detail data
    $data = AgentUtils::getGraphData($agent, [DAgentStatsType::GPU_TEMP, DAgentStatsType::GPU_UTIL]);
    $OBJECTS['gpuTemp'] = json_encode($data['sets']);
    $OBJECTS['gpuTempAvailable'] = (sizeof($data['sets']) > 0)?true:false;
    $OBJECTS['gpuTempXLabels'] = json_encode($data['xlabels']);
    $OBJECTS['gpuAxes'] = json_encode($data['axes']);

    $qF = new QueryFilter(Assignment::AGENT_ID, $agent->getId(), "=");
    $assignment = Factory::getAssignmentFactory()->filter([Factory::FILTER => $qF], true);
    $currentTask = 0;
    if ($assignment != null) {
      $currentTask = $assignment->getTaskId();
    }
    $OBJECTS['currentTask'] = $currentTask;

    $qF = new QueryFilter(AgentError::AGENT_ID, $agent->getId(), "=");
    $OBJECTS['errors'] = Factory::getAgentErrorFactory()->filter([Factory::FILTER => $qF]);

    $qF = new QueryFilter(Chunk::AGENT_ID, $agent->getId(), "=");
    $chunks = Factory::getChunkFactory()->filter([Factory::FILTER => $qF]);
    $timeSpent = 0;
    foreach ($chunks as $chunk) {
      $timeSpent += max($chunk->getSolveTime(), $chunk->getDispatchTime()) - $chunk->getDispatchTime();
    }
    $OBJECTS['chunks'] = $chunks;
    $OBJECTS['timeSpent'] = $timeSpent;
  }
}
else if (isset($_GET['new']) && $ACCESS_CONTROL->hasPermission(DAccessControl::CREATE_AGENT_ACCESS)) {
  $MENU->setActive("agents_new");
  $TEMPLATE = new Template("agents/new");
  $OBJECTS['pageTitle'] = "New Agent";
  $vouchers = Factory::getRegVoucherFactory()->filter([]);
  $OBJECTS['vouchers'] = $vouchers;
  $binaries = Factory::getAgentBinaryFactory()->filter([]);
  $OBJECTS['agentBinaries'] = $binaries;

  $url = explode("/", $_SERVER['PHP_SELF']);
  unset($url[sizeof($url) - 1]);
  $OBJECTS['apiUrl'] = Util::buildServerUrl() . implode("/", $url) . "/api/server.php";
  $OBJECTS['agentUrl'] = Util::buildServerUrl() . implode("/", $url) . "/agents.php?download=";
}
else {
  $OBJECTS['pageTitle'] = "Agents";

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
    $agent->setDevices(Util::compressDevices(explode("\n", $agent->getDevices())));
  }

  $OBJECTS['accessGroupAgents'] = $accessGroupAgents;
  $OBJECTS['agents'] = $agents;
  $OBJECTS['numAgents'] = sizeof($agents);
}

echo $TEMPLATE->render($OBJECTS);




