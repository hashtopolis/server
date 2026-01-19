<?php

use DBA\APiKey;
use DBA\QueryFilter;
use DBA\Factory;
use DBA\User;

require_once(dirname(__FILE__) . "/inc/startup/load.php");

if (!Login::getInstance()->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}

AccessControl::getInstance()->checkPermission(DViewControl::API_VIEW_PERM);

Template::loadInstance("api/index");
Menu::get()->setActive("users_api");

//catch actions here...
if (isset($_POST['action']) && CSRF::check($_POST['csrf'])) {
  if (UI::getNumMessages() == 0) {
    $apiHandler = new ApiHandler();
    $apiHandler->handle($_POST['action']);
    if (UI::getNumMessages() == 0) {
      Util::refresh();
    }
  }
}

if (isset($_GET['new'])) {
  Template::loadInstance("api/new");
  UI::add('pageTitle', "Create new API Group");
}
else if (isset($_GET['id'])) {
  $group = Factory::getApiGroupFactory()->get($_GET['id']);
  if ($group == null) {
    UI::printError("ERROR", "Invalid api group!");
  }
  else {
    $qF = new QueryFilter(ApiKey::API_GROUP_ID, $group->getId(), "=");
    $keys = Factory::getApiKeyFactory()->filter([Factory::FILTER => $qF]);
    if (MASK_API_KEYS) {
      $userID = Login::getInstance()->getUserID();
      foreach ($keys as $key) {
        if ($key->getUserId() != $userID) {
          $key->setAccessKey("******************************");
        }
      }
    }
    UI::add('keys', $keys);
    UI::add('sectionConstants', USection::getConstants());
    
    $section = USection::TEST;
    if (isset($_GET['section'])) {
      $section = $_GET['section'];
    }
    $currentSection = UApi::getSection($section);
    if ($currentSection == null) {
      UI::printError("ERROR", "Invalid section!");
    }
    UI::add('currentConstants', $currentSection->getConstants());
    UI::add('currentSection', $section);
    
    UI::add('group', $group);
    if ($group->getPermissions() == 'ALL') {
      UI::add('perm', 'ALL');
    }
    else {
      $json = json_decode($group->getPermissions(), true);
      if (isset($json[$section])) {
        $json = $json[$section];
      }
      else {
        $json = "{}";
      }
      UI::add('perm', new DataSet($json));
    }
    
    Template::loadInstance("api/detail");
    UI::add('pageTitle', "Details of API Group " . htmlentities($group->getName(), ENT_QUOTES, "UTF-8"));
  }
}
else if (isset($_GET['newkey'])) {
  Template::loadInstance("api/newkey");
  UI::add('users', Factory::getUserFactory()->filter([]));
  UI::add('groups', Factory::getApiGroupFactory()->filter([]));
  UI::add('pageTitle', "Create new API key");
}
else if (isset($_GET['keyId'])) {
  $key = Factory::getApiKeyFactory()->get($_GET['keyId']);
  if ($key == null) {
    UI::printError(UI::ERROR, "Invalid API key ID!");
  }
  if (MASK_API_KEYS) {
    $userID = Login::getInstance()->getUserID();
    $qF = new QueryFilter(User::USER_ID, $key->getUserId(), "=");
    $users = Factory::getUserFactory()->filter([Factory::FILTER => $qF]);
    if ($key->getUserId() != $userID) {
      $key->setAccessKey("******************************");
    }
  }
  else {
    $users = Factory::getUserFactory()->filter([]);
  }
  UI::add('key', $key);
  UI::add('users', $users);
  UI::add('groups', Factory::getApiGroupFactory()->filter([]));
  UI::add('pageTitle', "Edit API key");
  Template::loadInstance("api/key");
}
else {
  // determine keys and groups
  $groups = Factory::getApiGroupFactory()->filter([]);
  
  $apis = array();
  foreach ($groups as $group) {
    $apis[$group->getId()] = 0;
  }
  
  $allApiKeys = Factory::getApiKeyFactory()->filter([]);
  $userID = Login::getInstance()->getUserID();
  foreach ($allApiKeys as $apiKey) {
    $apis[$apiKey->getApiGroupId()]++;
    if (MASK_API_KEYS && $apiKey->getUserId() != $userID) {
      $apiKey->setAccessKey("******************************");
    }
  }
  
  UI::add('keys', $allApiKeys);
  UI::add('apis', new DataSet($apis));
  UI::add('groups', $groups);
  UI::add('pageTitle', "Api Groups");
}

echo Template::getInstance()->render(UI::getObjects());




