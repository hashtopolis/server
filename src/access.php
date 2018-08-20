<?php

use DBA\QueryFilter;
use DBA\User;
use DBA\Factory;

require_once(dirname(__FILE__) . "/inc/load.php");

/** @var array $OBJECTS */

if (!Login::getInstance()->isLoggedin()) {
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
  $group = Factory::getRightGroupFactory()->get($_GET['id']);
  if ($group == null) {
    UI::printError("ERROR", "Invalid permission group!");
  }
  else {
    $OBJECTS['group'] = $group;
    if ($group->getPermissions() == 'ALL') {
      $OBJECTS['perm'] = 'ALL';
    }
    else {
      $OBJECTS['perm'] = new DataSet(json_decode($group->getPermissions(), true));
    }

    $qF = new QueryFilter(User::RIGHT_GROUP_ID, $group->getId(), "=");
    $OBJECTS['users'] = Factory::getUserFactory()->filter([Factory::FILTER => $qF]);
    $constants = DAccessControl::getConstants();
    $constantsChecked = [];
    foreach ($constants as $constant) {
      if (is_array($constant)) {
        $constant = $constant[0];
      }
      if ($constant == DAccessControl::PUBLIC_ACCESS || $constant == DAccessControl::LOGIN_ACCESS) {
        // ignore public and login access
      }
      else {
        $constantsChecked[] = $constant;
      }
    }
    $OBJECTS['constants'] = $constantsChecked;

    $TEMPLATE = new Template("access/detail");
    $OBJECTS['pageTitle'] = "Details of Permission Group " . htmlentities($group->getGroupName(), ENT_QUOTES, "UTF-8");
  }
}
else {
  // determine members and agents
  $groups = Factory::getRightGroupFactory()->filter(array());

  $users = array();
  foreach ($groups as $group) {
    $users[$group->getId()] = 0;
  }

  $allUsers = Factory::getUserFactory()->filter(array());
  foreach ($allUsers as $user) {
    $users[$user->getRightGroupId()]++;
  }

  $OBJECTS['users'] = new DataSet($users);
  $OBJECTS['groups'] = $groups;
  $OBJECTS['pageTitle'] = "Permission Groups";
}

echo $TEMPLATE->render($OBJECTS);




