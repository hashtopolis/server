<?php

use DBA\APiKey;
use DBA\QueryFilter;

require_once(dirname(__FILE__) . "/inc/load.php");

/** @var Login $LOGIN */
/** @var array $OBJECTS */

if (!$LOGIN->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}

$ACCESS_CONTROL->checkPermission(DViewControl::API_VIEW_PERM);

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
  $OBJECTS['pageTitle'] = "Create new API Group";
}
else if (isset($_GET['id'])) {
  $group = $FACTORIES::getApiGroupFactory()->get($_GET['id']);
  if ($group == null) {
    UI::printError("ERROR", "Invalid api group!");
  }
  else {
    $qF = new QueryFilter(ApiKey::API_GROUP_ID, $group->getId(), "=");
    $OBJECTS['keys'] = $FACTORIES::getApiKeyFactory()->filter(array($FACTORIES::FILTER => $qF));

    $sectionConstants = USection::getConstants();
    $OBJECTS['sectionConstants'] = $sectionConstants;

    $section = USection::TEST;
    if (isset($_GET['section'])) {
      $section = $_GET['section'];
    }
    $currentSection = UApi::getSection($section);
    if ($currentSection == null) {
      UI::printError("ERROR", "Invalid section!");
    }
    $OBJECTS['currentConstants'] = $currentSection->getConstants();
    $OBJECTS['currentSection'] = $section;

    $OBJECTS['group'] = $group;
    if ($group->getPermissions() == 'ALL') {
      $OBJECTS['perm'] = 'ALL';
    }
    else {
      $json = json_decode($group->getPermissions(), true);
      if (isset($json[$section])) {
        $json = $json[$section];
      }
      else {
        $json = "{}";
      }
      $OBJECTS['perm'] = new DataSet($json);
    }

    $TEMPLATE = new Template("api/detail");
    $OBJECTS['pageTitle'] = "Details of API Group " . htmlentities($group->getName(), ENT_QUOTES, "UTF-8");
  }
}
else if(isset($_GET['newkey'])){
  $TEMPLATE = new Template("api/newkey");
  $OBJECTS['users'] = $FACTORIES::getUserFactory()->filter([]);
  $OBJECTS['groups'] = $FACTORIES::getApiGroupFactory()->filter([]);
  $OBJECTS['pageTitle'] = "Create new API key";
}
else if(isset($_GET['keyId'])){
  $key = $FACTORIES::getApiKeyFactory()->get($_GET['keyId']);
  if($key == null){
    UI::printError(UI::ERROR, "Invalid API key ID!");
  }
  $OBJECTS['key'] = $key;
  $OBJECTS['users'] = $FACTORIES::getUserFactory()->filter([]);
  $OBJECTS['groups'] = $FACTORIES::getApiGroupFactory()->filter([]);
  $TEMPLATE = new Template("api/key");
  $OBJECTS['pageTitle'] = "Edit API key";
}
else {
  // determine keys and groups
  $groups = $FACTORIES::getApiGroupFactory()->filter(array());

  $apis = array();
  foreach ($groups as $group) {
    $apis[$group->getId()] = 0;
  }

  $allApiKeys = $FACTORIES::getApiKeyFactory()->filter(array());
  foreach ($allApiKeys as $apiKey) {
    $apis[$apiKey->getApiGroupId()]++;
  }

  $OBJECTS['keys'] = $FACTORIES::getApiKeyFactory()->filter([]);
  $OBJECTS['apis'] = new DataSet($apis);
  $OBJECTS['groups'] = $groups;
  $OBJECTS['pageTitle'] = "Api Groups";
}

echo $TEMPLATE->render($OBJECTS);




