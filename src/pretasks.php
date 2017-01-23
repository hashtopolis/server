<?php

use DBA\File;
use DBA\JoinFilter;
use DBA\OrderFilter;
use DBA\QueryFilter;
use DBA\Task;
use DBA\TaskFile;

require_once(dirname(__FILE__) . "/inc/load.php");

/** @var Login $LOGIN */
/** @var array $OBJECTS */

if (!$LOGIN->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF']));
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
$taskList = $FACTORIES::getTaskFactory()->filter(array('filter' => $qF, 'order' => array($oF1, $oF2)));
$tasks = array();
for($z=0;$z<sizeof($taskList);$z++){
  $set = new DataSet();
  $task = $taskList[$z];
  $set->addValue('Task', $taskList[$z]);
  
  $qF = new QueryFilter(TaskFile::TASK_ID, $task->getId(), "=", $FACTORIES::getTaskFileFactory());
  $jF = new JoinFilter($FACTORIES::getTaskFileFactory(), TaskFile::FILE_ID, File::FILE_ID);
  $joinedFiles = $FACTORIES::getFileFactory()->filter(array('filter' => $qF, 'join' => $jF));
  $sizes = 0;
  $secret = false;
  for($x=0;$x<sizeof($joinedFiles['File']);$x++){
    $file = \DBA\Util::cast($joinedFiles['File'][$x], \DBA\File::class);
    $sizes += $file->getSize();
    if($file->getSecret() == '1'){
      $secret = true;
    }
  }
  
  $set->addValue('numFiles', sizeof($joinedFiles['File']));
  $set->addValue('filesSize', $sizes);
  $set->addValue('fileSecret', $secret);
  
  $tasks[] = $set;
}
$OBJECTS['tasks'] = $tasks;

/*$res = AbstractModelFactory::getDB()->query("SELECT tasks.id,tasks.name,tasks.color,tasks.attackcmd,tasks.priority,taskfiles.fcount AS filescount,IFNULL(taskfiles.fsize,0) AS filesize,IFNULL(taskfiles.secret,0) AS secret FROM tasks LEFT JOIN (SELECT taskfiles.task,COUNT(1) AS fcount,SUM(files.size) AS fsize,MAX(files.secret) AS secret FROM taskfiles JOIN files ON files.id=taskfiles.file GROUP BY taskfiles.task) taskfiles ON taskfiles.task=tasks.id WHERE tasks.hashlist IS NULL ORDER by tasks.priority DESC, tasks.id ASC");
$res = $res->fetchAll();
$tasks = array();
foreach ($res as $task) {
  $set = new DataSet();
  $set->setValues($task);
  $tasks[] = $set;
}

$OBJECTS['tasks'] = $tasks;
$OBJECTS['numPretasks'] = sizeof($tasks);*/

echo $TEMPLATE->render($OBJECTS);




