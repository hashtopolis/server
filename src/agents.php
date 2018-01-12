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

require_once(dirname(__FILE__) . "/inc/load.php");

/** @var Login $LOGIN */
/** @var array $OBJECTS */
/** @var DataSet $CONFIG */

if (isset($_GET['download'])) {
  $binaryId = $_GET['download'];
  $agentHandler = new AgentHandler();
  $agentHandler->handle(DAgentAction::DOWNLOAD_AGENT);
}

if (!$LOGIN->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}
else if ($LOGIN->getLevel() < DAccessLevel::USER) {
  $TEMPLATE = new Template("restricted");
  die($TEMPLATE->render($OBJECTS));
}

$TEMPLATE = new Template("agents/index");
$MENU->setActive("agents_list");

//catch actions here...
if (isset($_POST['action']) && CSRF::check($_POST['csrf'])) {
  $binaryId = @$_POST['binary'];
  $agentHandler = new AgentHandler($_POST['agentId']);
  $agentHandler->handle($_POST['action']);
  if (UI::getNumMessages() == 0) {
    Util::refresh();
  }
}


// load groups for user
$qF = new QueryFilter(AccessGroupUser::USER_ID, $LOGIN->getUserID(), "=");
$userGroups = $FACTORIES::getAccessGroupUserFactory()->filter(array($FACTORIES::FILTER => $qF));
$accessGroupIds = array();
foreach ($userGroups as $userGroup) {
  $accessGroupIds[] = $userGroup->getAccessGroupId();
}

/*$qF = new QueryFilter(TaskWrapper::HASHLIST_ID, null, "<>");
$allTasks = $FACTORIES::getTaskWrapperFactory()->filter(array($FACTORIES::FILTER => $qF));*/

if (isset($_GET['id'])) {
  //show agent detail
  $TEMPLATE = new Template("agents/detail");
  $agent = $FACTORIES::getAgentFactory()->get($_GET['id']);
  if (!$agent) {
    UI::printError("ERROR", "Agent not found!");
  }
  else if (!AccessUtils::userCanAccessAgent($agent, $LOGIN->getUser())) {
    UI::printError("ERROR", "No access to this agent!");
  }
  else {
    $OBJECTS['agent'] = $agent;
    $OBJECTS['users'] = $FACTORIES::getUserFactory()->filter(array());
    
    // load all tasks which are valid for this agent
    $OBJECTS['allTasks'] = TaskUtils::getBestTask($agent->getId(), true);
    
    $qF = new QueryFilter(Assignment::AGENT_ID, $agent->getId(), "=");
    $assignment = $FACTORIES::getAssignmentFactory()->filter(array($FACTORIES::FILTER => $qF), true);
    $currentTask = 0;
    if ($assignment != null) {
      $currentTask = $assignment->getTaskId();
    }
    $OBJECTS['currentTask'] = $currentTask;
    
    $qF = new QueryFilter(AgentError::AGENT_ID, $agent->getId(), "=");
    $OBJECTS['errors'] = $FACTORIES::getAgentErrorFactory()->filter(array($FACTORIES::FILTER => $qF));
    
    $qF = new QueryFilter(Chunk::AGENT_ID, $agent->getId(), "=");
    $chunks = $FACTORIES::getChunkFactory()->filter(array($FACTORIES::FILTER => $qF));
    $timeSpent = 0;
    foreach ($chunks as $chunk) {
      $timeSpent += max($chunk->getSolveTime(), $chunk->getDispatchTime()) - $chunk->getDispatchTime();
    }
    $OBJECTS['chunks'] = $chunks;
    $OBJECTS['timeSpent'] = $timeSpent;
  }
}
else if (isset($_GET['new']) && $LOGIN->getLevel() >= DAccessLevel::SUPERUSER) {
  $MENU->setActive("agents_new");
  $TEMPLATE = new Template("agents/new");
  $vouchers = $FACTORIES::getRegVoucherFactory()->filter(array());
  $OBJECTS['vouchers'] = $vouchers;
  $binaries = $FACTORIES::getAgentBinaryFactory()->filter(array());
  $OBJECTS['agentBinaries'] = $binaries;
  
  $url = explode("/", $_SERVER['PHP_SELF']);
  unset($url[sizeof($url) - 1]);
  $OBJECTS['apiUrl'] = Util::buildServerUrl() . implode("/", $url) . "/api/server.php";
  $OBJECTS['agentUrl'] = Util::buildServerUrl() . implode("/", $url) . "/agents.php?download=";
}
else {
  // load all agents which are in an access group the user has access to
  $oF = new OrderFilter(Agent::AGENT_ID, "ASC", $FACTORIES::getAgentFactory());
  $qF = new ContainFilter(AccessGroupAgent::ACCESS_GROUP_ID, $accessGroupIds, $FACTORIES::getAccessGroupAgentFactory());
  $jF = new JoinFilter($FACTORIES::getAccessGroupAgentFactory(), Agent::AGENT_ID, AccessGroupAgent::AGENT_ID);
  $joined = $FACTORIES::getAgentFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::ORDER => $oF, $FACTORIES::JOIN => $jF));
  /** @var $agents Agent[] */
  $agents = $joined[$FACTORIES::getAgentFactory()->getModelName()];
  /** @var $accessGroups AccessGroupAgent[] */
  $accessGroups = $joined[$FACTORIES::getAccessGroupAgentFactory()->getModelName()];
  $accessGroupAgents = new DataSet();
  foreach ($agents as $agent) {
    $qF = new QueryFilter(AccessGroupAgent::AGENT_ID, $agent->getId(), "=", $FACTORIES::getAccessGroupAgentFactory());
    $jF = new JoinFilter($FACTORIES::getAccessGroupAgentFactory(), AccessGroup::ACCESS_GROUP_ID, AccessGroupAgent::ACCESS_GROUP_ID);
    $joined = $FACTORIES::getAccessGroupFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::JOIN => $jF));
    $accessGroupAgents->addValue($agent->getId(), $joined[$FACTORIES::getAccessGroupFactory()->getModelName()]);
    $agent->setDevices(explode("\n", $agent->getDevices()));
  }
  
  $OBJECTS['accessGroupAgents'] = $accessGroupAgents;
  $OBJECTS['agents'] = $agents;
  $OBJECTS['numAgents'] = sizeof($agents);
}

echo $TEMPLATE->render($OBJECTS);




