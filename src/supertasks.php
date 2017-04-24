<?php

use DBA\JoinFilter;
use DBA\QueryFilter;
use DBA\SupertaskTask;
use DBA\Task;

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

$TEMPLATE = new Template("supertasks/index");
$MENU->setActive("tasks_super");

//catch actions here...
if (isset($_POST['action'])) {
  $supertaskHandler = new SupertaskHandler();
  $supertaskHandler->handle($_POST['action']);
  if (UI::getNumMessages() == 0) {
    Util::refresh();
  }
}

if (isset($_GET['create'])) {
  $MENU->setActive("tasks_supernew");
  $TEMPLATE = new Template("supertasks/create");
  $qF = new QueryFilter(Task::HASHLIST_ID, null, "=");
  $OBJECTS['preTasks'] = $FACTORIES::getTaskFactory()->filter(array($FACTORIES::FILTER => $qF));
}
else if (isset($_GET['id']) && isset($_GET['new'])) {
  $TEMPLATE = new Template("supertasks/new");
  $supertask = $FACTORIES::getSupertaskFactory()->get($_GET['id']);
  $OBJECTS['orig'] = $supertask->getId();
  $OBJECTS['lists'] = $FACTORIES::getHashlistFactory()->filter(array());
}
else if (isset($_GET['id'])) {
  $TEMPLATE = new Template("supertasks/detail");
  $supertask = $FACTORIES::getSupertaskFactory()->get($_GET['id']);
  if ($supertask == null) {
    UI::printError("ERROR", "Invalid supertask ID!");
  }
  $qF = new QueryFilter(SupertaskTask::SUPERTASK_ID, $supertask->getId(), "=", $FACTORIES::getSupertaskTaskFactory());
  $jF = new JoinFilter($FACTORIES::getSupertaskTaskFactory(), SupertaskTask::TASK_ID, Task::TASK_ID);
  $tasks = $FACTORIES::getTaskFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::JOIN => $jF));
  $OBJECTS['tasks'] = $tasks[$FACTORIES::getTaskFactory()->getModelName()];
  $OBJECTS['supertask'] = $supertask;
}
else {
  $supertasks = $FACTORIES::getSupertaskFactory()->filter(array());
  $supertaskTasks = new DataSet();
  foreach ($supertasks as $supertask) {
    $qF = new QueryFilter(SupertaskTask::SUPERTASK_ID, $supertask->getId(), "=", $FACTORIES::getSupertaskTaskFactory());
    $jF = new JoinFilter($FACTORIES::getSupertaskTaskFactory(), SupertaskTask::TASK_ID, Task::TASK_ID);
    $joinedTasks = $FACTORIES::getTaskFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::JOIN => $jF));
    $tasks = $joinedTasks[$FACTORIES::getTaskFactory()->getModelName()];
    $supertaskTasks->addValue($supertask->getId(), $tasks);
  }
  $OBJECTS['tasks'] = $supertaskTasks;
  $OBJECTS['supertasks'] = $supertasks;
}

echo $TEMPLATE->render($OBJECTS);




