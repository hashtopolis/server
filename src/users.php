<?php

use DBA\QueryFilter;
use DBA\AccessGroupUser;
use DBA\AccessGroup;
use DBA\JoinFilter;
use DBA\Factory;

require_once(dirname(__FILE__) . "/inc/load.php");

/** @var array $OBJECTS */

if (!Login::getInstance()->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}

$ACCESS_CONTROL->checkPermission(DViewControl::USERS_VIEW_PERM);

$TEMPLATE = new Template("users/index");
$MENU->setActive("users_list");

//catch actions here...
if (isset($_POST['action']) && CSRF::check($_POST['csrf'])) {
  $usersHandler = new UsersHandler();
  $usersHandler->handle($_POST['action']);
  if (UI::getNumMessages() == 0) {
    Util::refresh();
  }
}

if (isset($_GET['new'])) {
  $TEMPLATE = new Template("users/new");
  $MENU->setActive("users_new");
  $OBJECTS['groups'] = Factory::getRightGroupFactory()->filter([]);
  $OBJECTS['pageTitle'] = "Create User";
}
else if (isset($_GET['id'])) {
  $user = Factory::getUserFactory()->get($_GET['id']);
  if ($user == null) {
    UI::printError("ERROR", "Invalid user!");
  }
  else {
    $OBJECTS['user'] = $user;
    $OBJECTS['groups'] = Factory::getRightGroupFactory()->filter([]);

    $qF = new QueryFilter(AccessGroupUser::USER_ID, $user->getId(), "=", Factory::getAccessGroupUserFactory());
    $jF = new JoinFilter(Factory::getAccessGroupUserFactory(), AccessGroup::ACCESS_GROUP_ID, AccessGroupUser::ACCESS_GROUP_ID);
    $joinedGroups = Factory::getAccessGroupFactory()->filter([Factory::FILTER => $qF, Factory::JOIN => $jF]);
    $OBJECTS['accessGroups'] = $joinedGroups[Factory::getAccessGroupFactory()->getModelName()];

    $TEMPLATE = new Template("users/detail");
    $OBJECTS['pageTitle'] = "User details for " . $user->getUsername();
  }
}
else {
  $users = array();
  $res = Factory::getUserFactory()->filter([]);
  foreach ($res as $entry) {
    $set = new DataSet();
    $set->addValue('user', $entry);
    $set->addValue('group', Factory::getRightGroupFactory()->get($entry->getRightGroupId()));
    $users[] = $set;
  }

  $OBJECTS['allUsers'] = $users;
  $OBJECTS['numUsers'] = sizeof($users);
  $OBJECTS['pageTitle'] = "Users";
}

echo $TEMPLATE->render($OBJECTS);




