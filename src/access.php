<?php

use Hashtopolis\dba\QueryFilter;
use Hashtopolis\dba\models\User;
use Hashtopolis\dba\Factory;
use Hashtopolis\inc\CSRF;
use Hashtopolis\inc\DataSet;
use Hashtopolis\inc\defines\DAccessControl;
use Hashtopolis\inc\defines\DViewControl;
use Hashtopolis\inc\handlers\AccessControlHandler;
use Hashtopolis\inc\Login;
use Hashtopolis\inc\Menu;
use Hashtopolis\inc\templating\Template;
use Hashtopolis\inc\UI;
use Hashtopolis\inc\Util;
use Hashtopolis\inc\utils\AccessControl;

require_once(dirname(__FILE__) . "/inc/startup/load.php");

if (!Login::getInstance()->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}

AccessControl::getInstance()->checkPermission(DViewControl::ACCESS_VIEW_PERM);

Template::loadInstance("access/index");
Menu::get()->setActive("users_access");

//catch actions here...
if (isset($_POST['action']) && CSRF::check($_POST['csrf'])) {
  $accessControlHandler = new AccessControlHandler();
  $accessControlHandler->handle($_POST['action']);
  if (UI::getNumMessages() == 0) {
    Util::refresh();
  }
}

if (isset($_GET['new'])) {
  Template::loadInstance("access/new");
  UI::add('pageTitle', "Create new Permission Group");
}
else if (isset($_GET['id'])) {
  $group = Factory::getRightGroupFactory()->get($_GET['id']);
  if ($group == null) {
    UI::printError("ERROR", "Invalid permission group!");
  }
  else {
    UI::add('group', $group);
    if ($group->getPermissions() == 'ALL') {
      UI::add('perm', 'ALL');
    }
    else {
      UI::add('perm', new DataSet(json_decode($group->getPermissions(), true)));
    }
    
    $qF = new QueryFilter(User::RIGHT_GROUP_ID, $group->getId(), "=");
    UI::add('users', Factory::getUserFactory()->filter([Factory::FILTER => $qF]));
    $constants = DAccessControl::getConstants();
    $constantsChecked = [];
    foreach ($constants as $constant) {
      if (is_array($constant)) {
        $constant = $constant[0];
      }
      if ($constant != DAccessControl::PUBLIC_ACCESS && $constant != DAccessControl::LOGIN_ACCESS) {
        $constantsChecked[] = $constant;
      }
    }
    UI::add('constants', $constantsChecked);
    UI::add('pageTitle', "Details of Permission Group " . htmlentities($group->getGroupName(), ENT_QUOTES, "UTF-8"));
    Template::loadInstance("access/detail");
  }
}
else {
  // determine members and agents
  $groups = Factory::getRightGroupFactory()->filter([]);
  
  $users = array();
  foreach ($groups as $group) {
    $users[$group->getId()] = 0;
  }
  
  $allUsers = Factory::getUserFactory()->filter([]);
  foreach ($allUsers as $user) {
    $users[$user->getRightGroupId()]++;
  }
  
  UI::add('users', new DataSet($users));
  UI::add('groups', $groups);
  UI::add('pageTitle', "Permission Groups");
}

echo Template::getInstance()->render(UI::getObjects());




