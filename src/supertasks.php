<?php

use Hashtopolis\dba\JoinFilter;
use Hashtopolis\dba\models\Pretask;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\dba\OrderFilter;
use Hashtopolis\dba\ContainFilter;
use Hashtopolis\dba\models\SupertaskPretask;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\File;
use Hashtopolis\dba\models\FilePretask;
use Hashtopolis\inc\CSRF;
use Hashtopolis\inc\DataSet;
use Hashtopolis\inc\defines\DAccessControl;
use Hashtopolis\inc\defines\DViewControl;
use Hashtopolis\inc\handlers\SupertaskHandler;
use Hashtopolis\inc\Login;
use Hashtopolis\inc\Menu;
use Hashtopolis\inc\templating\Template;
use Hashtopolis\inc\UI;
use Hashtopolis\inc\Util;
use Hashtopolis\inc\utils\AccessControl;
use Hashtopolis\inc\utils\FileUtils;
use Hashtopolis\inc\utils\HashlistUtils;

require_once(dirname(__FILE__) . "/inc/startup/load.php");

if (!Login::getInstance()->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}

AccessControl::getInstance()->checkPermission(DViewControl::SUPERTASKS_VIEW_PERM);

Template::loadInstance("supertasks/index");
Menu::get()->setActive("tasks_super");

//catch actions here...
if (isset($_POST['action']) && CSRF::check($_POST['csrf'])) {
  $supertaskHandler = new SupertaskHandler();
  $supertaskHandler->handle($_POST['action']);
  if (UI::getNumMessages() == 0) {
    Util::refresh();
  }
}

if (isset($_GET['create']) && $_GET['create'] == "new" && AccessControl::getInstance()->hasPermission(DAccessControl::CREATE_SUPERTASK_ACCESS)) {
  Menu::get()->setActive("tasks_supernew");
  Template::loadInstance("supertasks/create");
  $qF = new QueryFilter(Pretask::IS_MASK_IMPORT, 0, "=");
  UI::add('preTasks', Factory::getPretaskFactory()->filter([Factory::FILTER => $qF]));
  UI::add('pageTitle', "Create Supertask");
}
else if (isset($_GET['create']) && $_GET['create'] == "import" && AccessControl::getInstance()->hasPermission(DAccessControl::CREATE_SUPERTASK_ACCESS)) {
  Menu::get()->setActive("tasks_superimport");
  Template::loadInstance("supertasks/import");
  
  $arr = FileUtils::loadFilesByCategory(Login::getInstance()->getUser(), []);
  UI::add('wordlists', $arr[1]);
  UI::add('rules', $arr[0]);
  UI::add('other', $arr[2]);
  
  UI::add('crackerBinaryTypes', Factory::getCrackerBinaryTypeFactory()->filter([]));
  UI::add('pageTitle', "Import Supertask from Masks");
}
else if (isset($_GET['id']) && isset($_GET['new']) && AccessControl::getInstance()->hasPermission(DAccessControl::RUN_TASK_ACCESS)) {
  Template::loadInstance("supertasks/new");
  $supertask = Factory::getSupertaskFactory()->get($_GET['id']);
  UI::add('orig', $supertask->getId());
  UI::add('lists', HashlistUtils::getHashlists(Login::getInstance()->getUser()));
  UI::add('binaries', Factory::getCrackerBinaryTypeFactory()->filter([]));
  $versions = Factory::getCrackerBinaryFactory()->filter([]);
  usort($versions, ["Hashtopolis\inc\Util", "versionComparisonBinary"]);
  UI::add('versions', $versions);
  UI::add('pageTitle', "Issue Supertask");
}
else if (isset($_GET['id'])) {
  Template::loadInstance("supertasks/detail");
  $supertask = Factory::getSupertaskFactory()->get($_GET['id']);
  if ($supertask == null) {
    UI::printError("ERROR", "Invalid supertask ID!");
  }
  $qF = new QueryFilter(SupertaskPretask::SUPERTASK_ID, $supertask->getId(), "=", Factory::getSupertaskPretaskFactory());
  $jF = new JoinFilter(Factory::getSupertaskPretaskFactory(), SupertaskPretask::PRETASK_ID, Pretask::PRETASK_ID);
  $tasks = Factory::getPretaskFactory()->filter([Factory::FILTER => $qF, Factory::JOIN => $jF]);
  UI::add('tasks', $tasks[Factory::getPretaskFactory()->getModelName()]);
  UI::add('supertask', $supertask);
  UI::add('pageTitle', "Supertask details for " . $supertask->getSupertaskName());
  
  $pretasks = $tasks[Factory::getPretaskFactory()->getModelName()];
  $pretaskIds = Util::arrayOfIds($pretasks);
  
  $qF1 = new ContainFilter(Pretask::PRETASK_ID, $pretaskIds,null, true);
  $allOtherPretasks = Factory::getPretaskFactory()->filter([Factory::FILTER => $qF1]);
  UI::add('allOtherPretasks', $allOtherPretasks);
  
  $allPretasks = Factory::getPretaskFactory()->filter([]);
  $allPretasksIds = Util::arrayOfIds($allPretasks);
  $pretaskAuxiliaryKeyspace = new DataSet();
  //  retrieve per pretask: an array containing line counts of the files associated with that pretask
  foreach ($allPretasksIds as $pretaskId) {
    $qF1 = new QueryFilter(FilePretask::PRETASK_ID, $pretaskId,"=", Factory::getFilePretaskFactory());
    $jF1 = new JoinFilter(Factory::getFilePretaskFactory(), File::FILE_ID, FilePretask::FILE_ID);
    $files = Factory::getFileFactory()->filter([Factory::FILTER => $qF1, Factory::JOIN => $jF1]);
    $files = $files[Factory::getFileFactory()->getModelName()];
    
    $lineCountProduct = 1;
    foreach ($files as $file) {
      $lineCount = $file->getLineCount();
      if ($lineCount !== null) {
        $lineCountProduct = $lineCountProduct * $lineCount;
      }
    }
    $pretaskAuxiliaryKeyspace->addValue($pretaskId, $lineCountProduct);
  }
  // auxiliary keyspace is the keyspace formed by the product of the line counts of the files linked to a pretask
  UI::add('pretaskAuxiliaryKeyspace', $pretaskAuxiliaryKeyspace);
  
  $benchmarka0 = 0;
  $benchmarka3 = 0;
  if (isset($_POST['benchmarka0'])) {
    $benchmarka0 = $_POST['benchmarka0'];
  }
  if (isset($_POST['benchmarka3'])) {
    $benchmarka3 = $_POST['benchmarka3'];
  }
  
  if (isset($_GET['benchmarka0'])) {
    $benchmarka0 = $_GET['benchmarka0'];
  }
  if (isset($_GET['benchmarka3'])) {
    $benchmarka3 = $_GET['benchmarka3'];
  }
  UI::add('benchmarka0', $benchmarka0);
  UI::add('benchmarka3', $benchmarka3);
}
else {
  $supertasks = Factory::getSupertaskFactory()->filter([]);
  $supertaskTasks = new DataSet();
  foreach ($supertasks as $supertask) {
    $qF = new QueryFilter(SupertaskPretask::SUPERTASK_ID, $supertask->getId(), "=", Factory::getSupertaskPretaskFactory());
    $jF = new JoinFilter(Factory::getSupertaskPretaskFactory(), SupertaskPretask::PRETASK_ID, Pretask::PRETASK_ID);
    $oF = new OrderFilter(Pretask::PRIORITY, "DESC");
    $joinedTasks = Factory::getPretaskFactory()->filter([Factory::FILTER => $qF, Factory::JOIN => $jF, Factory::ORDER => $oF]);
    $tasks = $joinedTasks[Factory::getPretaskFactory()->getModelName()];
    $supertaskTasks->addValue($supertask->getId(), $tasks);
    
  }
  UI::add('tasks', $supertaskTasks);
  UI::add('supertasks', $supertasks);
  UI::add('pageTitle', "Supertasks");
}

echo Template::getInstance()->render(UI::getObjects());




