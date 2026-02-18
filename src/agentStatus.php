<?php

use DBA\AccessGroupAgent;
use DBA\AccessGroupUser;
use DBA\Assignment;
use DBA\Chunk;
use DBA\ContainFilter;
use DBA\OrderFilter;
use DBA\QueryFilter;
use DBA\AgentStat;
use DBA\Agent;
use DBA\Factory;

require_once(dirname(__FILE__) . "/inc/startup/load.php");

if (!Login::getInstance()->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}

AccessControl::getInstance()->checkPermission(DViewControl::AGENTS_VIEW_PERM);

Template::loadInstance("agents/status");
Menu::get()->setActive("agents_status");

// load groups for user
$qF = new QueryFilter(AccessGroupUser::USER_ID, Login::getInstance()->getUserID(), "=");
$userGroups = Factory::getAccessGroupUserFactory()->filter([Factory::FILTER => $qF]);
$accessGroupIds = array();
foreach ($userGroups as $userGroup) {
  $accessGroupIds[] = $userGroup->getAccessGroupId();
}

UI::add('pageTitle', "Agents Status");

// load all agents which are in an access group the user has access to
$qF = new ContainFilter(AccessGroupAgent::ACCESS_GROUP_ID, $accessGroupIds);
$accessGroupAgents = Factory::getAccessGroupAgentFactory()->filter([Factory::FILTER => $qF]);
$agentIds = array();
foreach ($accessGroupAgents as $accessGroupAgent) {
  $agentIds[] = $accessGroupAgent->getAgentId();
}

$oF = new OrderFilter(Agent::AGENT_ID, "ASC");
$qF = new ContainFilter(Agent::AGENT_ID, $agentIds);
UI::add('agents', Factory::getAgentFactory()->filter([Factory::FILTER => $qF, Factory::ORDER => $oF]));

$oF1 = new OrderFilter(AgentStat::AGENT_ID, "ASC");
$oF2 = new OrderFilter(AgentStat::TIME, "DESC");
$qF1 = new ContainFilter(AgentStat::AGENT_ID, $agentIds);
$qF2 = new QueryFilter(AgentStat::STAT_TYPE, DAgentStatsType::GPU_UTIL, "=");
$qF3 = new QueryFilter(AgentStat::TIME, time() - SConfig::getInstance()->getVal(DConfig::AGENT_TIMEOUT), ">");
$stats = Factory::getAgentStatFactory()->filter([Factory::FILTER => [$qF1, $qF2, $qF3], Factory::ORDER => [$oF1, $oF2]]);
$agentStats = new DataSet();
foreach ($stats as $stat) {
  if ($agentStats->getVal($stat->getAgentId()) === false) {
    $agentStats->addValue($stat->getAgentId(), $stat);
  }
}
UI::add('deviceStats', $agentStats);

$oF1 = new OrderFilter(AgentStat::AGENT_ID, "ASC");
$oF2 = new OrderFilter(AgentStat::TIME, "DESC");
$qF1 = new ContainFilter(AgentStat::AGENT_ID, $agentIds);
$qF2 = new QueryFilter(AgentStat::STAT_TYPE, DAgentStatsType::GPU_TEMP, "=");
$qF3 = new QueryFilter(AgentStat::TIME, time() - SConfig::getInstance()->getVal(DConfig::AGENT_TIMEOUT), ">");
$stats = Factory::getAgentStatFactory()->filter([Factory::FILTER => [$qF1, $qF2, $qF3], Factory::ORDER => [$oF1, $oF2]]);
$agentStats = new DataSet();
foreach ($stats as $stat) {
  if ($agentStats->getVal($stat->getAgentId()) === false) {
    $agentStats->addValue($stat->getAgentId(), $stat);
  }
}
UI::add('deviceTemps', $agentStats);

$oF1 = new OrderFilter(AgentStat::AGENT_ID, "ASC");
$oF2 = new OrderFilter(AgentStat::TIME, "DESC");
$qF1 = new ContainFilter(AgentStat::AGENT_ID, $agentIds);
$qF2 = new QueryFilter(AgentStat::STAT_TYPE, DAgentStatsType::CPU_UTIL, "=");
$qF3 = new QueryFilter(AgentStat::TIME, time() - SConfig::getInstance()->getVal(DConfig::AGENT_TIMEOUT), ">");
$stats = Factory::getAgentStatFactory()->filter([Factory::FILTER => [$qF1, $qF2, $qF3], Factory::ORDER => [$oF1, $oF2]]);
$agentStats = new DataSet();
foreach ($stats as $stat) {
  if ($agentStats->getVal($stat->getAgentId()) === false) {
    $agentStats->addValue($stat->getAgentId(), $stat);
  }
}
UI::add('cpuStats', $agentStats);

$agentTasks = new DataSet();
$agentSpeeds = new DataSet();
$agentChunks = new DataSet();
$agentAssignments = new DataSet();
$agents = Factory::getAgentFactory()->filter([Factory::FILTER => $qF, Factory::ORDER => $oF]);
foreach ($agents as $agent) {
  $qF1 = new QueryFilter(Chunk::AGENT_ID, $agent->getId(), "=");
  $qF2 = new QueryFilter(Chunk::SPEED, 0, ">");
  $chunks = Factory::getChunkFactory()->filter([Factory::FILTER => [$qF1, $qF2]]);
  foreach ($chunks as $chunk) {
    $agentTasks->addValue($agent->getId(), $chunk->getTaskId());
    $agentSpeeds->addValue($agent->getId(), $chunk->getSpeed());
    $agentChunks->addValue($agent->getId(), $chunk->getId());
  }
  $qF = new QueryFilter(Assignment::AGENT_ID, $agent->getId(), "=");
  $assignment = Factory::getAssignmentFactory()->filter([Factory::FILTER => $qF], true);
  if ($assignment != null) {
    $agentAssignments->addValue($agent->getId(), $assignment->getTaskId());
  }
}
UI::add('agentAssignments', $agentAssignments);
UI::add('agentSpeeds', $agentSpeeds);
UI::add('agentTasks', $agentTasks);
UI::add('agentChunks', $agentChunks);

echo Template::getInstance()->render(UI::getObjects());




