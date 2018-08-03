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
use DBA\AgentStat;

require_once(dirname(__FILE__) . "/inc/load.php");

/** @var Login $LOGIN */
/** @var array $OBJECTS */
/** @var DataSet $CONFIG */

if (!$LOGIN->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}

$ACCESS_CONTROL->checkPermission(DViewControl::AGENTS_VIEW_PERM);

$TEMPLATE = new Template("agents/status");
$MENU->setActive("agents_status");

// load groups for user
$qF = new QueryFilter(AccessGroupUser::USER_ID, $LOGIN->getUserID(), "=");
$userGroups = $FACTORIES::getAccessGroupUserFactory()->filter(array($FACTORIES::FILTER => $qF));
$accessGroupIds = array();
foreach ($userGroups as $userGroup) {
  $accessGroupIds[] = $userGroup->getAccessGroupId();
}

$OBJECTS['pageTitle'] = "Agents Status";

// load all agents which are in an access group the user has access to
$qF = new ContainFilter(AccessGroupAgent::ACCESS_GROUP_ID, $accessGroupIds);
$accessGroupAgents = $FACTORIES::getAccessGroupAgentFactory()->filter(array($FACTORIES::FILTER => $qF));
$agentIds = array();
foreach ($accessGroupAgents as $accessGroupAgent) {
  $agentIds[] = $accessGroupAgent->getAgentId();
}

$oF1 = new OrderFilter(AgentStat::AGENT_ID, "ASC");
$oF2 = new OrderFilter(AgentStat::TIME, "DESC");
$qF1 = new ContainFilter(AgentStat::AGENT_ID, $agentIds);
$qF2 = new QueryFilter(AgentStat::STAT_TYPE, DAgentStatsType::GPU_UTIL, "=");
$qF3 = new QueryFilter(AgentStat::TIME, time() - $CONFIG->getVal(DConfig::AGENT_TIMEOUT), ">");
$stats = $FACTORIES::getAgentStatFactory()->filter(array($FACTORIES::FILTER => [$qF1, $qF2, $qF3], $FACTORIES::ORDER => [$oF1, $oF2]));
$OBJECTS['agentIds'] = $agentIds;
$agentStats = new DataSet();
foreach($stats as $stat){
  if($agentStats->getVal($stat->getAgentId()) === false){
    $agentStats->addValue($stat->getAgentId(), $stat);
  }
}
$OBJECTS['stats'] = $agentStats;

echo $TEMPLATE->render($OBJECTS);




