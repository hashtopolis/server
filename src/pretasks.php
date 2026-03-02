<?php

use Hashtopolis\dba\models\File;
use Hashtopolis\dba\models\FilePretask;
use Hashtopolis\dba\JoinFilter;
use Hashtopolis\dba\OrderFilter;
use Hashtopolis\dba\models\Pretask;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\dba\models\SupertaskPretask;
use Hashtopolis\dba\Factory;
use Hashtopolis\inc\CSRF;
use Hashtopolis\inc\DataSet;
use Hashtopolis\inc\defines\DAccessControl;
use Hashtopolis\inc\defines\DConfig;
use Hashtopolis\inc\defines\DViewControl;
use Hashtopolis\inc\handlers\PretaskHandler;
use Hashtopolis\inc\Login;
use Hashtopolis\inc\Menu;
use Hashtopolis\inc\SConfig;
use Hashtopolis\inc\templating\Template;
use Hashtopolis\inc\UI;
use Hashtopolis\inc\Util;
use Hashtopolis\inc\utils\AccessControl;
use Hashtopolis\inc\utils\AccessUtils;
use Hashtopolis\inc\utils\FileUtils;
use Hashtopolis\inc\utils\PretaskUtils;
use Hashtopolis\inc\utils\TaskUtils;

require_once(dirname(__FILE__) . "/inc/startup/load.php");

if (!Login::getInstance()->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}

AccessControl::getInstance()->checkPermission(DViewControl::PRETASKS_VIEW_PERM);

Template::loadInstance("pretasks/index");
Menu::get()->setActive("tasks_pre");

//catch actions here...
if (isset($_POST['action']) && CSRF::check($_POST['csrf'])) {
  $pretaskHandler = new PretaskHandler();
  $pretaskHandler->handle($_POST['action']);
  if (UI::getNumMessages() == 0) {
    Util::refresh();
  }
}

if (isset($_GET['id'])) {
  $pretask = Factory::getPretaskFactory()->get($_GET['id']);
  if ($pretask === null) {
    UI::printError(UI::ERROR, "Invalid preconfigured task!");
  }
  Template::loadInstance("pretasks/detail");
  UI::add('pretask', $pretask);
  
  $qF = new QueryFilter(FilePretask::PRETASK_ID, $pretask->getId(), "=", Factory::getFilePretaskFactory());
  $jF = new JoinFilter(Factory::getFilePretaskFactory(), FilePretask::FILE_ID, File::FILE_ID);
  $joinedFiles = Factory::getFileFactory()->filter([Factory::FILTER => $qF, Factory::JOIN => $jF]);
  UI::add('attachedFiles', $joinedFiles[Factory::getFileFactory()->getModelName()]);
  
  $isUsed = false;
  $qF = new QueryFilter(SupertaskPretask::PRETASK_ID, $pretask->getId(), "=");
  $supertaskTasks = Factory::getSupertaskPretaskFactory()->filter([Factory::FILTER => $qF]);
  if (sizeof($supertaskTasks) > 0) {
    $isUsed = true;
  }
  UI::add('isUsed', $isUsed);
  UI::add('pageTitle', "Preconfigured task details for " . $pretask->getTaskName());
}
else if (isset($_GET['new']) && AccessControl::getInstance()->hasPermission(DAccessControl::CREATE_PRETASK_ACCESS)) {
  Template::loadInstance("pretasks/new");
  Menu::get()->setActive("tasks_prenew");
  
  UI::add('accessGroups', AccessUtils::getAccessGroupsOfUser(Login::getInstance()->getUser()));
  $accessGroupIds = Util::arrayOfIds(UI::get('accessGroups'));
  
  $orig = 0;
  $origTask = null;
  $origType = 0;
  $hashlistId = 0;
  $copy = null;
  if (isset($_GET["copy"])) {
    //copied from a task
    $copy = Factory::getPretaskFactory()->get($_GET['copy']);
    if ($copy != null) {
      $orig = $copy->getId();
      $origTask = clone $copy;
      $origType = 2;
      $copy->setId(0);
      $match = array();
      if (preg_match('/\(copy([0-9]+)\)/i', $copy->getTaskName(), $match)) {
        $name = $copy->getTaskName();
        $name = str_replace($match[0], "(copy" . (++$match[1]) . ")", $name);
        $copy->setTaskName($name);
      }
      else {
        $copy->setTaskName($copy->getTaskName() . " (copy1)");
      }
    }
  }
  else if (isset($_GET['copyTask'])) {
    $copy = Factory::getTaskFactory()->get($_GET['copyTask']);
    if ($copy != null) {
      $orig = $copy->getId();
      $origType = 1;
      $origTask = $copy;
      $copy = PretaskUtils::getFromTask($copy);
    }
  }
  if ($copy === null) {
    $copy = PretaskUtils::getDefault();
  }
  
  $origFiles = array();
  if ($origType == 1) {
    $origFiles = Util::arrayOfIds(TaskUtils::getFilesOfTask($origTask));
  }
  else if ($origType == 2) {
    $origFiles = Util::arrayOfIds(TaskUtils::getFilesOfPretask($origTask));
  }
  
  $arr = FileUtils::loadFilesByCategory(Login::getInstance()->getUser(), $origFiles);
  UI::add('wordlists', $arr[1]);
  UI::add('rules', $arr[0]);
  UI::add('other', $arr[2]);
  
  UI::add('crackerBinaryTypes', Factory::getCrackerBinaryTypeFactory()->filter([]));
  UI::add('pageTitle', "Create preconfigured Task");
  UI::add('copy', $copy);
}
else {
  $queryFilters = array();
  if (SConfig::getInstance()->getVal(DConfig::HIDE_IMPORT_MASKS) == 1) {
    $queryFilters[] = new QueryFilter(Pretask::IS_MASK_IMPORT, 0, "=");
  }
  $oF1 = new OrderFilter(Pretask::PRIORITY, "DESC");
  $oF2 = new OrderFilter(Pretask::PRETASK_ID, "ASC");
  $taskList = Factory::getPretaskFactory()->filter([Factory::FILTER => $queryFilters, Factory::ORDER => [$oF1, $oF2]]);
  $tasks = array();
  for ($z = 0; $z < sizeof($taskList); $z++) {
    $set = new DataSet();
    $pretask = $taskList[$z];
    $set->addValue('Task', $taskList[$z]);
    
    $qF = new QueryFilter(FilePretask::PRETASK_ID, $pretask->getId(), "=", Factory::getFilePretaskFactory());
    $jF = new JoinFilter(Factory::getFilePretaskFactory(), FilePretask::FILE_ID, File::FILE_ID);
    $joinedFiles = Factory::getFileFactory()->filter([Factory::FILTER => $qF, Factory::JOIN => $jF]);
    /** @var $files File[] */
    $files = $joinedFiles[Factory::getFileFactory()->getModelName()];
    $sizes = 0;
    $secret = false;
    foreach ($files as $file) {
      $sizes += $file->getSize();
      if ($file->getIsSecret() == 1) {
        $secret = true;
      }
    }
    
    $isUsed = false;
    $qF = new QueryFilter(SupertaskPretask::PRETASK_ID, $pretask->getId(), "=");
    $supertaskTasks = Factory::getSupertaskPretaskFactory()->filter([Factory::FILTER => $qF]);
    if (sizeof($supertaskTasks) > 0) {
      $isUsed = true;
    }
    
    $set->addValue('numFiles', sizeof($files));
    $set->addValue('filesSize', $sizes);
    $set->addValue('fileSecret', $secret);
    $set->addValue('isUsed', $isUsed);
    
    $tasks[] = $set;
  }
  UI::add('tasks', $tasks);
  UI::add('pageTitle', "Preconfigured Tasks");
}

echo Template::getInstance()->render(UI::getObjects());




