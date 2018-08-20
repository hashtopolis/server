<?php

use DBA\APiKey;
use DBA\QueryFilter;
use DBA\Factory;

require_once(dirname(__FILE__) . "/inc/load.php");

if (!Login::getInstance()->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}

AccessControl::getInstance()->checkPermission(DViewControl::API_VIEW_PERM);

$TEMPLATE = new Template("api/index");
$MENU->setActive("users_api");

//catch actions here...
if (isset($_POST['action']) && CSRF::check($_POST['csrf'])) {
  $apiHandler = new ApiHandler();
  $apiHandler->handle($_POST['action']);
  if (UI::getNumMessages() == 0) {
    Util::refresh();
  }
}

if (isset($_GET['new'])) {
  $TEMPLATE = new Template("api/new");
  UI::add('pageTitle', "Create new API Group");
}
else if (isset($_GET['id'])) {
  $group = Factory::getApiGroupFactory()->get($_GET['id']);
  if ($group == null) {
    UI::printError("ERROR", "Invalid api group!");
  }
  else {
    $qF = new QueryFilter(ApiKey::API_GROUP_ID, $group->getId(), "=");
    UI::add('keys', Factory::getApiKeyFactory()->filter([Factory::FILTER => $qF]));
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

    $TEMPLATE = new Template("api/detail");
    UI::add('pageTitle', "Details of API Group " . htmlentities($group->getName(), ENT_QUOTES, "UTF-8"));
  }
}
else if(isset($_GET['newkey'])){
  $TEMPLATE = new Template("api/newkey");
  UI::add('users', Factory::getUserFactory()->filter([]));
  UI::add('groups', Factory::getApiGroupFactory()->filter([]));
  UI::add('pageTitle', "Create new API key");
}
else if(isset($_GET['keyId'])){
  $key = Factory::getApiKeyFactory()->get($_GET['keyId']);
  if($key == null){
    UI::printError(UI::ERROR, "Invalid API key ID!");
  }
  UI::add('key', $key);
  UI::add('users', Factory::getUserFactory()->filter([]));
  UI::add('groups', Factory::getApiGroupFactory()->filter([]));
  $TEMPLATE = new Template("api/key");
  UI::add('pageTitle', "Edit API key");
}
else {
  // determine keys and groups
  $groups = Factory::getApiGroupFactory()->filter([]);

  $apis = array();
  foreach ($groups as $group) {
    $apis[$group->getId()] = 0;
  }

  $allApiKeys = Factory::getApiKeyFactory()->filter([]);
  foreach ($allApiKeys as $apiKey) {
    $apis[$apiKey->getApiGroupId()]++;
  }

  UI::add('keys', Factory::getApiKeyFactory()->filter([]));
  UI::add('apis', new DataSet($apis));
  UI::add('groups', $groups);
  UI::add('pageTitle', "Api Groups");
}

echo $TEMPLATE->render(UI::getObjects());




