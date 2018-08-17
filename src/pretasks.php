<?php

use DBA\File;
use DBA\FilePretask;
use DBA\JoinFilter;
use DBA\OrderFilter;
use DBA\Pretask;
use DBA\QueryFilter;
use DBA\SupertaskPretask;
use DBA\ContainFilter;
use DBA\FileTask;

require_once(dirname(__FILE__) . "/inc/load.php");

/** @var Login $LOGIN */
/** @var array $OBJECTS */
/** @var DataSet $CONFIG */

if (!$LOGIN->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}

$ACCESS_CONTROL->checkPermission(DViewControl::PRETASKS_VIEW_PERM);

$TEMPLATE = new Template("pretasks/index");
$MENU->setActive("tasks_pre");

//catch actions here...
if (isset($_POST['action']) && CSRF::check($_POST['csrf'])) {
  $pretaskHandler = new PretaskHandler();
  $pretaskHandler->handle($_POST['action']);
  if (UI::getNumMessages() == 0) {
    Util::refresh();
  }
}

if (isset($_GET['id'])) {
  $pretask = $FACTORIES::getPretaskFactory()->get($_GET['id']);
  if ($pretask === null) {
    UI::printError(UI::ERROR, "Invalid preconfigured task!");
  }
  $TEMPLATE = new Template("pretasks/detail");
  $OBJECTS['pretask'] = $pretask;

  $qF = new QueryFilter(FilePretask::PRETASK_ID, $pretask->getId(), "=", $FACTORIES::getFilePretaskFactory());
  $jF = new JoinFilter($FACTORIES::getFilePretaskFactory(), FilePretask::FILE_ID, File::FILE_ID);
  $joinedFiles = $FACTORIES::getFileFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::JOIN => $jF));
  $OBJECTS['attachedFiles'] = $joinedFiles[$FACTORIES::getFileFactory()->getModelName()];

  $isUsed = false;
  $qF = new QueryFilter(SupertaskPretask::PRETASK_ID, $pretask->getId(), "=");
  $supertaskTasks = $FACTORIES::getSupertaskPretaskFactory()->filter(array($FACTORIES::FILTER => $qF));
  if (sizeof($supertaskTasks) > 0) {
    $isUsed = true;
  }
  $OBJECTS['isUsed'] = $isUsed;
  $OBJECTS['pageTitle'] = "Preconfigured task details for " . $pretask->getTaskName();
}
else if (isset($_GET['new']) && $ACCESS_CONTROL->hasPermission(DAccessControl::CREATE_PRETASK_ACCESS)) {
  $TEMPLATE = new Template("pretasks/new");
  $MENU->setActive("tasks_prenew");

  $OBJECTS['accessGroups'] = AccessUtils::getAccessGroupsOfUser($LOGIN->getUser());
  $accessGroupIds = Util::arrayOfIds($OBJECTS['accessGroups']);
  $origFiles = array();
  $orig = 0;
  if ($orig > 0) {
    if ($origType == 1) {
      $qF = new QueryFilter(FileTask::TASK_ID, $orig, "=");
      $ff = $FACTORIES::getFileTaskFactory()->filter(array($FACTORIES::FILTER => $qF));
      foreach ($ff as $f) {
        $origFiles[] = $f->getFileId();
      }
    }
    else {
      $qF = new QueryFilter(FilePretask::PRETASK_ID, $orig, "=");
      $ff = $FACTORIES::getFilePretaskFactory()->filter(array($FACTORIES::FILTER => $qF));
      foreach ($ff as $f) {
        $origFiles[] = $f->getFileId();
      }
    }
  }
  $oF = new OrderFilter(File::FILENAME, "ASC");
  $qF = new ContainFilter(File::ACCESS_GROUP_ID, $accessGroupIds);
  $allFiles = $FACTORIES::getFileFactory()->filter(array($FACTORIES::ORDER => $oF));
  $rules = [];
  $wordlists = [];
  $other = [];
  foreach ($allFiles as $singleFile) {
    $set = new DataSet();
    $checked = "0";
    if (in_array($singleFile->getId(), $origFiles)) {
      $checked = "1";
    }
    $set->addValue('checked', $checked);
    $set->addValue('file', $singleFile);
    if ($singleFile->getFileType() == DFileType::RULE) {
      $rules[] = $set;
    }
    else if($singleFile->getFileType() == DFileType::WORDLIST){
      $wordlists[] = $set;
    }
    else if($singleFile->getFileType() == DFileType::OTHER){
      $other[] = $set;
    }
  }
  $OBJECTS['wordlists'] = $wordlists;
  $OBJECTS['rules'] = $rules;
  $OBJECTS['other'] = $other;

  $OBJECTS['crackerBinaryTypes'] = $FACTORIES::getCrackerBinaryTypeFactory()->filter(array());
  $OBJECTS['pageTitle'] = "Create preconfigured Task";
}
else {
  $queryFilters = array();
  if ($CONFIG->getVal(DConfig::HIDE_IMPORT_MASKS) == 1) {
    $queryFilters[] = new QueryFilter(Pretask::IS_MASK_IMPORT, 0, "=");
  }
  $oF1 = new OrderFilter(Pretask::PRIORITY, "DESC");
  $oF2 = new OrderFilter(Pretask::PRETASK_ID, "ASC");
  $taskList = $FACTORIES::getPretaskFactory()->filter(array($FACTORIES::FILTER => $queryFilters, $FACTORIES::ORDER => array($oF1, $oF2)));
  $tasks = array();
  for ($z = 0; $z < sizeof($taskList); $z++) {
    $set = new DataSet();
    $pretask = $taskList[$z];
    $set->addValue('Task', $taskList[$z]);

    $qF = new QueryFilter(FilePretask::PRETASK_ID, $pretask->getId(), "=", $FACTORIES::getFilePretaskFactory());
    $jF = new JoinFilter($FACTORIES::getFilePretaskFactory(), FilePretask::FILE_ID, File::FILE_ID);
    $joinedFiles = $FACTORIES::getFileFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::JOIN => $jF));
    /** @var $files File[] */
    $files = $joinedFiles[$FACTORIES::getFileFactory()->getModelName()];
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
    $supertaskTasks = $FACTORIES::getSupertaskPretaskFactory()->filter(array($FACTORIES::FILTER => $qF));
    if (sizeof($supertaskTasks) > 0) {
      $isUsed = true;
    }

    $set->addValue('numFiles', sizeof($files));
    $set->addValue('filesSize', $sizes);
    $set->addValue('fileSecret', $secret);
    $set->addValue('isUsed', $isUsed);

    $tasks[] = $set;
  }
  $OBJECTS['tasks'] = $tasks;
  $OBJECTS['pageTitle'] = "Preconfigured Tasks";
}

echo $TEMPLATE->render($OBJECTS);




