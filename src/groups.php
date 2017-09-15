<?php

require_once(dirname(__FILE__) . "/inc/load.php");

/** @var Login $LOGIN */
/** @var array $OBJECTS */

if (!$LOGIN->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}
else if ($LOGIN->getLevel() < DAccessLevel::ADMINISTRATOR) {
  $TEMPLATE = new Template("errors/restricted");
  die($TEMPLATE->render($OBJECTS));
}

$TEMPLATE = new Template("groups/index");
$MENU->setActive("users_groups");

//catch actions here...
if (isset($_POST['action']) && Util::checkCSRF($_POST['csrf'])) {
  $accessGroupHandler = new AccessGroupHandler();
  $accessGroupHandler->handle($_POST['action']);
  if (UI::getNumMessages() == 0) {
    Util::refresh();
  }
}

if (isset($_GET['new'])) {
  $TEMPLATE = new Template("groups/new");
}
else if (isset($_GET['id'])) {
  $group = $FACTORIES::getAccessGroupFactory()->get($_GET['id']);
  if ($group == null) {
    UI::printError("ERROR", "Invalid group!");
  }
  else {
    $OBJECTS['group'] = $group;
    
    $TEMPLATE = new Template("groups/detail");
  }
}
else {
  // determine members and agents
  $userList = $FACTORIES::getAccessGroupUserFactory()->filter(array());
  $users = new DataSet();
  foreach ($userList as $user) {
    $users->addValue($user->getAccessGroupId(), $users->getVal($user->getAccessGroupId()) + 1);
  }
  $OBJECTS['users'] = $users;
  
  $agentList = $FACTORIES::getAccessGroupAgentFactory()->filter(array());
  $agents = new DataSet();
  foreach ($agentList as $agent) {
    $agents->addValue($agent->getAccessGroupId(), $agents->getVal($agent->getAccessGroupId()) + 1);
  }
  $OBJECTS['agents'] = $agents;
  
  
  $OBJECTS['groups'] = $FACTORIES::getAccessGroupFactory()->filter(array());
}

echo $TEMPLATE->render($OBJECTS);




