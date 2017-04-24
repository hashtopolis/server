<?php

use DBA\File;
use DBA\JoinFilter;
use DBA\OrderFilter;
use DBA\QueryFilter;
use DBA\SupertaskTask;
use DBA\Task;
use DBA\TaskFile;

require_once(dirname(__FILE__) . "/inc/load.php");

/** @var Login $LOGIN */
/** @var array $OBJECTS */

if (!$LOGIN->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}
else if ($LOGIN->getLevel() < DAccessLevel::READ_ONLY) {
  $TEMPLATE = new Template("restricted");
  die($TEMPLATE->render($OBJECTS));
}

$TEMPLATE = new Template("pretasks");
$MENU->setActive("tasks_pre");

$oF1 = new OrderFilter(Task::PRIORITY, "DESC");
$qF = new QueryFilter(Task::HASHLIST_ID, null, "=");
$oF2 = new OrderFilter(Task::TASK_ID, "ASC");
$taskList = $FACTORIES::getTaskFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::ORDER => array($oF1, $oF2)));
$tasks = array();
for ($z = 0; $z < sizeof($taskList); $z++) {
  $set = new DataSet();
  $task = $taskList[$z];
  $set->addValue('Task', $taskList[$z]);
  
  $qF = new QueryFilter(TaskFile::TASK_ID, $task->getId(), "=", $FACTORIES::getTaskFileFactory());
  $jF = new JoinFilter($FACTORIES::getTaskFileFactory(), TaskFile::FILE_ID, File::FILE_ID);
  $joinedFiles = $FACTORIES::getFileFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::JOIN => $jF));
  $sizes = 0;
  $secret = false;
  for ($x = 0; $x < sizeof($joinedFiles[$FACTORIES::getFileFactory()->getModelName()]); $x++) {
    $file = \DBA\Util::cast($joinedFiles[$FACTORIES::getFileFactory()->getModelName()][$x], \DBA\File::class);
    $sizes += $file->getSize();
    if ($file->getSecret() == '1') {
      $secret = true;
    }
  }
  
  $isUsed = false;
  $qF = new QueryFilter(SupertaskTask::TASK_ID, $task->getId(), "=");
  $supertaskTasks = $FACTORIES::getSupertaskTaskFactory()->filter(array($FACTORIES::FILTER => $qF));
  if (sizeof($supertaskTasks) > 0) {
    $isUsed = true;
  }
  
  $set->addValue('numFiles', sizeof($joinedFiles['File']));
  $set->addValue('filesSize', $sizes);
  $set->addValue('fileSecret', $secret);
  $set->addValue('isUsed', $isUsed);
  
  $tasks[] = $set;
}
$OBJECTS['tasks'] = $tasks;

echo $TEMPLATE->render($OBJECTS);




