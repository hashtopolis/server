<?php

use DBA\AccessGroupAgent;
use DBA\AccessGroupUser;
use DBA\ContainFilter;
use DBA\OrderFilter;
use DBA\QueryFilter;
use DBA\AgentStat;
use DBA\Agent;
use DBA\Factory;

require_once(dirname(__FILE__) . "/inc/load.php");

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

echo Template::getInstance()->render(UI::getObjects());




