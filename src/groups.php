<?php

use DBA\AccessGroupAgent;
use DBA\AccessGroupUser;
use DBA\Agent;
use DBA\ContainFilter;
use DBA\JoinFilter;
use DBA\QueryFilter;
use DBA\User;
use DBA\Factory;

require_once(dirname(__FILE__) . "/inc/load.php");

/** @var Login $LOGIN */
/** @var array $OBJECTS */

if (!$LOGIN->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}

$ACCESS_CONTROL->checkPermission(DViewControl::GROUPS_VIEW_PERM);

$TEMPLATE = new Template("groups/index");
$MENU->setActive("users_groups");

//catch actions here...
if (isset($_POST['action']) && CSRF::check($_POST['csrf'])) {
  $accessGroupHandler = new AccessGroupHandler();
  $accessGroupHandler->handle($_POST['action']);
  if (UI::getNumMessages() == 0) {
    Util::refresh();
  }
}

if (isset($_GET['new'])) {
  $TEMPLATE = new Template("groups/new");
  $OBJECTS['pageTitle'] = "Create Group";
}
else if (isset($_GET['id'])) {
  $group = Factory::getAccessGroupFactory()->get($_GET['id']);
  if ($group == null) {
    UI::printError("ERROR", "Invalid group!");
  }
  else {
    $OBJECTS['group'] = $group;

    $jF = new JoinFilter(Factory::getAccessGroupUserFactory(), User::USER_ID, AccessGroupUser::USER_ID);
    $qF = new QueryFilter(AccessGroupUser::ACCESS_GROUP_ID, $group->getId(), "=", Factory::getAccessGroupUserFactory());
    $joinedUsers = Factory::getUserFactory()->filter([Factory::FILTER => $qF, Factory::JOIN => $jF]);
    /** @var $users User[] */
    $users = $joinedUsers[Factory::getUserFactory()->getModelName()];
    $OBJECTS['users'] = $users;
    $excludedUsers = Util::arrayOfIds($users);

    $jF = new JoinFilter(Factory::getAccessGroupAgentFactory(), Agent::AGENT_ID, AccessGroupAgent::AGENT_ID);
    $qF = new QueryFilter(AccessGroupAgent::ACCESS_GROUP_ID, $group->getId(), "=", Factory::getAccessGroupAgentFactory());
    $joinedAgents = Factory::getAgentFactory()->filter([Factory::FILTER => $qF, Factory::JOIN => $jF]);
    /** @var $agents Agent[] */
    $agents = $joinedAgents[Factory::getAgentFactory()->getModelName()];
    $OBJECTS['agents'] = $agents;
    $excludedAgents = Util::arrayOfIds($agents);

    $qF = new ContainFilter(User::USER_ID, $excludedUsers, Factory::getUserFactory(), true);
    $OBJECTS['allUsers'] = Factory::getUserFactory()->filter([Factory::FILTER => $qF]);
    $qF = new ContainFilter(Agent::AGENT_ID, $excludedAgents, Factory::getAgentFactory(), true);
    $OBJECTS['allAgents'] = Factory::getAgentFactory()->filter([Factory::FILTER => $qF]);
    $TEMPLATE = new Template("groups/detail");
    $OBJECTS['pageTitle'] = "Details of Group " . htmlentities($group->getGroupName(), ENT_QUOTES, "UTF-8");
  }
}
else {
  // determine members and agents
  $userList = Factory::getAccessGroupUserFactory()->filter([]);
  $users = new DataSet();
  foreach ($userList as $user) {
    $users->addValue($user->getAccessGroupId(), $users->getVal($user->getAccessGroupId()) + 1);
  }

  $agentList = Factory::getAccessGroupAgentFactory()->filter([]);
  $agents = new DataSet();
  foreach ($agentList as $agent) {
    $agents->addValue($agent->getAccessGroupId(), $agents->getVal($agent->getAccessGroupId()) + 1);
  }

  $OBJECTS['groups'] = Factory::getAccessGroupFactory()->filter([]);
  foreach ($OBJECTS['groups'] as $group) {
    if ($users->getVal($group->getId()) === false) {
      $users->addValue($group->getId(), 0);
    }
    if ($agents->getVal($group->getId()) === false) {
      $agents->addValue($group->getId(), 0);
    }
  }

  $OBJECTS['agents'] = $agents;
  $OBJECTS['users'] = $users;
  $OBJECTS['pageTitle'] = "Groups";
}

echo $TEMPLATE->render($OBJECTS);




