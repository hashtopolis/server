<?php

use DBA\AccessGroupAgent;
use DBA\AccessGroupUser;
use DBA\Agent;
use DBA\ContainFilter;
use DBA\JoinFilter;
use DBA\QueryFilter;
use DBA\User;

require_once(dirname(__FILE__) . "/inc/load.php");

/** @var Login $LOGIN */
/** @var array $OBJECTS */

if (!$LOGIN->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}

$ACCESS_CONTROL->checkPermission(DViewControl::ACCESS_VIEW_PERM);

$TEMPLATE = new Template("access/index");
$MENU->setActive("users_access");

//catch actions here...
if (isset($_POST['action']) && CSRF::check($_POST['csrf'])) {
  $accessControlHandler = new AccessControlHandler();
  $accessControlHandler->handle($_POST['action']);
  if (UI::getNumMessages() == 0) {
    Util::refresh();
  }
}

if (isset($_GET['new'])) {
  $TEMPLATE = new Template("access/new");
  $OBJECTS['pageTitle'] = "Create new Permission Group";
}
else if (isset($_GET['id'])) {
  $group = $FACTORIES::getRightGroupFactory()->get($_GET['id']);
  if ($group == null) {
    UI::printError("ERROR", "Invalid permission group!");
  }
  else {
    $OBJECTS['group'] = $group;
    
    $jF = new JoinFilter($FACTORIES::getAccessGroupUserFactory(), User::USER_ID, AccessGroupUser::USER_ID);
    $qF = new QueryFilter(AccessGroupUser::ACCESS_GROUP_ID, $group->getId(), "=", $FACTORIES::getAccessGroupUserFactory());
    $joinedUsers = $FACTORIES::getUserFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::JOIN => $jF));
    /** @var $users User[] */
    $users = $joinedUsers[$FACTORIES::getUserFactory()->getModelName()];
    $OBJECTS['users'] = $users;
    $excludedUsers = Util::arrayOfIds($users);
    
    $jF = new JoinFilter($FACTORIES::getAccessGroupAgentFactory(), Agent::AGENT_ID, AccessGroupAgent::AGENT_ID);
    $qF = new QueryFilter(AccessGroupAgent::ACCESS_GROUP_ID, $group->getId(), "=", $FACTORIES::getAccessGroupAgentFactory());
    $joinedAgents = $FACTORIES::getAgentFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::JOIN => $jF));
    /** @var $agents Agent[] */
    $agents = $joinedAgents[$FACTORIES::getAgentFactory()->getModelName()];
    $OBJECTS['agents'] = $agents;
    $excludedAgents = Util::arrayOfIds($agents);
    
    $qF = new ContainFilter(User::USER_ID, $excludedUsers, $FACTORIES::getUserFactory(), true);
    $OBJECTS['allUsers'] = $FACTORIES::getUserFactory()->filter(array($FACTORIES::FILTER => $qF));
    $qF = new ContainFilter(Agent::AGENT_ID, $excludedAgents, $FACTORIES::getAgentFactory(), true);
    $OBJECTS['allAgents'] = $FACTORIES::getAgentFactory()->filter(array($FACTORIES::FILTER => $qF));
    $TEMPLATE = new Template("access/detail");
    $OBJECTS['pageTitle'] = "Details of Permission Group " . htmlentities($group->getGroupName(), ENT_QUOTES, "UTF-8");
  }
}
else {
  // determine members and agents
  $groups = $FACTORIES::getRightGroupFactory()->filter(array());
  
  $users = array();
  foreach ($groups as $group) {
    $users[$group->getId()] = 0;
  }
  
  $allUsers = $FACTORIES::getUserFactory()->filter(array());
  foreach ($allUsers as $user) {
    $users[$user->getRightGroupId()]++;
  }
  
  $OBJECTS['users'] = $users;
  $OBJECTS['groups'] = $groups;
  $OBJECTS['pageTitle'] = "Permission Groups";
}

echo $TEMPLATE->render($OBJECTS);




