<?php

use Hashtopolis\dba\models\File;
use Hashtopolis\dba\OrderFilter;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\dba\ContainFilter;
use Hashtopolis\dba\Factory;
use Hashtopolis\inc\CSRF;
use Hashtopolis\inc\DataSet;
use Hashtopolis\inc\defines\DAccessControl;
use Hashtopolis\inc\defines\DViewControl;
use Hashtopolis\inc\handlers\FileHandler;
use Hashtopolis\inc\HTException;
use Hashtopolis\inc\Login;
use Hashtopolis\inc\Menu;
use Hashtopolis\inc\templating\Template;
use Hashtopolis\inc\UI;
use Hashtopolis\inc\Util;
use Hashtopolis\inc\utils\AccessControl;
use Hashtopolis\inc\utils\AccessUtils;
use Hashtopolis\inc\utils\FileUtils;

require_once(dirname(__FILE__) . "/inc/startup/load.php");

if (!Login::getInstance()->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}

AccessControl::getInstance()->checkPermission(DViewControl::FILES_VIEW_PERM);

Template::loadInstance("files/index");
Menu::get()->setActive("files");

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
  try {
    $file = FileUtils::getFile($_GET['edit'], Login::getInstance()->getUser());
    if ($file == null) {
      UI::addMessage(UI::ERROR, "Invalid file ID!");
    }
    else {
      UI::add('file', $file);
      Template::loadInstance("files/edit");
      UI::add('pageTitle', "Edit File " . $file->getFilename());
      UI::add('accessGroups', AccessUtils::getAccessGroupsOfUser(Login::getInstance()->getUser()));
    }
  }
  catch (HTException $e) {
    UI::addMessage(UI::ERROR, "Failed to retrieve file: " . $e->getMessage());
  }
}
else {
  $qF1 = new QueryFilter(File::FILE_TYPE, array_search($view, ['dict', 'rule', 'other']), "=");
  $qF2 = new ContainFilter(File::ACCESS_GROUP_ID, Util::arrayOfIds(AccessUtils::getAccessGroupsOfUser(Login::getInstance()->getUser())));
  $oF = new OrderFilter(File::FILENAME, "ASC");
  UI::add('fileType', "Other Files");
  if ($view == 'dict') {
    UI::add('fileType', "Wordlists");
  }
  else if ($view == 'rule') {
    UI::add('fileType', "Rules");
  }
  UI::add('files', Factory::getFileFactory()->filter([Factory::FILTER => [$qF1, $qF2], Factory::ORDER => $oF]));
  UI::add('impfiles', Util::scanImportDirectory());
  UI::add('pageTitle', "Files");
  $accessGroups = Factory::getAccessGroupFactory()->filter([]);
  $groups = new DataSet();
  foreach ($accessGroups as $accessGroup) {
    $groups->addValue($accessGroup->getId(), $accessGroup);
  }
  UI::add('accessGroups', $groups);
  UI::add('allAccessGroups', $accessGroups);
}
UI::add('view', $view);

echo Template::getInstance()->render(UI::getObjects());




