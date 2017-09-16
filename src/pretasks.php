<?php

use DBA\File;
use DBA\FilePretask;
use DBA\JoinFilter;
use DBA\OrderFilter;
use DBA\Pretask;
use DBA\QueryFilter;
use DBA\SupertaskPretask;
use DBA\SupertaskTask;
use DBA\TaskFile;

require_once(dirname(__FILE__) . "/inc/load.php");

/** @var Login $LOGIN */
/** @var array $OBJECTS */
/** @var DataSet $CONFIG */

if (!$LOGIN->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}
else if ($LOGIN->getLevel() < DAccessLevel::READ_ONLY) {
  $TEMPLATE = new Template("restricted");
  die($TEMPLATE->render($OBJECTS));
}

$TEMPLATE = new Template("pretasks/index");
$MENU->setActive("tasks_pre");


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
}

echo $TEMPLATE->render($OBJECTS);




