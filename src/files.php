<?php

use DBA\File;
use DBA\OrderFilter;
use DBA\QueryFilter;
use DBA\ContainFilter;
use DBA\Factory;

require_once(dirname(__FILE__) . "/inc/load.php");

/** @var array $OBJECTS */

if (!Login::getInstance()->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}

AccessControl::getInstance()->checkPermission(DViewControl::FILES_VIEW_PERM);

$TEMPLATE = new Template("files/index");
$MENU->setActive("files");
$message = "";

//catch actions here...
if (isset($_POST['action']) && CSRF::check($_POST['csrf'])) {
  $fileHandler = new FileHandler();
  $fileHandler->handle($_POST['action']);
  if (UI::getNumMessages() == 0) {
    Util::refresh();
  }
}

$view = "dict";
if (isset($_GET['view']) && in_array($_GET['view'], array('dict', 'rule', 'other'))) {
  $view = $_GET['view'];
}

if (isset($_GET['edit']) && AccessControl::getInstance()->hasPermission(DAccessControl::MANAGE_FILE_ACCESS)) {
  $file = FileUtils::getFile($_GET['edit'], Login::getInstance()->getUser());
  if ($file == null) {
    UI::addMessage(UI::ERROR, "Invalid file ID!");
  }
  else {
    $OBJECTS['file'] = $file;
    $TEMPLATE = new Template("files/edit");
    $OBJECTS['pageTitle'] = "Edit File " . $file->getFilename();
    $OBJECTS['accessGroups'] = AccessUtils::getAccessGroupsOfUser(Login::getInstance()->getUser());
  }
}
else {
  $qF1 = new QueryFilter(File::FILE_TYPE, array_search($view, array('dict', 'rule', 'other')), "=");
  $qF2 = new ContainFilter(File::ACCESS_GROUP_ID, Util::arrayOfIds(AccessUtils::getAccessGroupsOfUser(Login::getInstance()->getUser())));
  $oF = new OrderFilter(File::FILENAME, "ASC");
  $OBJECTS['fileType'] = "Other Files";
  if($view == 'dict'){
    $OBJECTS['fileType'] = "Wordlists";
  }
  else if($view == 'rule'){
    $OBJECTS['fileType'] = "Rules";
  }
  $OBJECTS['files'] = Factory::getFileFactory()->filter([Factory::FILTER => [$qF1, $qF2], Factory::ORDER => $oF]);;
  $OBJECTS['impfiles'] = Util::scanImportDirectory();
  $OBJECTS['pageTitle'] = "Files";
  $accessGroups = Factory::getAccessGroupFactory()->filter([]);
  $groups = new DataSet();
  foreach($accessGroups as $accessGroup){
    $groups->addValue($accessGroup->getId(), $accessGroup);
  }
  $OBJECTS['accessGroups'] = $groups;
  $OBJECTS['allAccessGroups'] = $accessGroups;
}
$OBJECTS['view'] = $view;

echo $TEMPLATE->render($OBJECTS);




