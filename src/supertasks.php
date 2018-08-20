<?php

use DBA\JoinFilter;
use DBA\Pretask;
use DBA\QueryFilter;
use DBA\OrderFilter;
use DBA\SupertaskPretask;
use DBA\Factory;

require_once(dirname(__FILE__) . "/inc/load.php");

/** @var array $OBJECTS */

if (!Login::getInstance()->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}

$ACCESS_CONTROL->checkPermission(DViewControl::SUPERTASKS_VIEW_PERM);

$TEMPLATE = new Template("supertasks/index");
$MENU->setActive("tasks_super");

//catch actions here...
if (isset($_POST['action']) && CSRF::check($_POST['csrf'])) {
  $supertaskHandler = new SupertaskHandler();
  $supertaskHandler->handle($_POST['action']);
  if (UI::getNumMessages() == 0) {
    Util::refresh();
  }
}

if (isset($_GET['create']) && $_GET['create'] == "new" && $ACCESS_CONTROL->hasPermission(DAccessControl::CREATE_SUPERTASK_ACCESS)) {
  $MENU->setActive("tasks_supernew");
  $TEMPLATE = new Template("supertasks/create");
  $qF = new QueryFilter(Pretask::IS_MASK_IMPORT, 0, "=");
  $OBJECTS['preTasks'] = Factory::getPretaskFactory()->filter([Factory::FILTER => $qF]);
  $OBJECTS['pageTitle'] = "Create Supertask";
}
else if (isset($_GET['create']) && $_GET['create'] == "import" && $ACCESS_CONTROL->hasPermission(DAccessControl::CREATE_SUPERTASK_ACCESS)) {
  $MENU->setActive("tasks_superimport");
  $TEMPLATE = new Template("supertasks/import");

  $OBJECTS['crackerBinaryTypes'] = Factory::getCrackerBinaryTypeFactory()->filter([]);
  $OBJECTS['pageTitle'] = "Import Supertask from Masks";
}
else if (isset($_GET['id']) && isset($_GET['new']) && $ACCESS_CONTROL->hasPermission(DAccessControl::RUN_TASK_ACCESS)) {
  $TEMPLATE = new Template("supertasks/new");
  $supertask = Factory::getSupertaskFactory()->get($_GET['id']);
  $OBJECTS['orig'] = $supertask->getId();
  $OBJECTS['lists'] = Factory::getHashlistFactory()->filter([]);
  $OBJECTS['binaries'] = Factory::getCrackerBinaryTypeFactory()->filter([]);
  $versions = Factory::getCrackerBinaryFactory()->filter([]);
  usort($versions, array("Util", "versionComparisonBinary"));
  $OBJECTS['versions'] = $versions;
  $OBJECTS['pageTitle'] = "Issue Supertask";
}
else if (isset($_GET['id'])) {
  $TEMPLATE = new Template("supertasks/detail");
  $supertask = Factory::getSupertaskFactory()->get($_GET['id']);
  if ($supertask == null) {
    UI::printError("ERROR", "Invalid supertask ID!");
  }
  $qF = new QueryFilter(SupertaskPretask::SUPERTASK_ID, $supertask->getId(), "=", Factory::getSupertaskPretaskFactory());
  $jF = new JoinFilter(Factory::getSupertaskPretaskFactory(), SupertaskPretask::PRETASK_ID, Pretask::PRETASK_ID);
  $tasks = Factory::getPretaskFactory()->filter([Factory::FILTER => $qF, Factory::JOIN => $jF]);
  $OBJECTS['tasks'] = $tasks[Factory::getPretaskFactory()->getModelName()];
  $OBJECTS['supertask'] = $supertask;
  $OBJECTS['pageTitle'] = "Supertask details for " . $supertask->getSupertaskName();
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
  $OBJECTS['tasks'] = $supertaskTasks;
  $OBJECTS['supertasks'] = $supertasks;
  $OBJECTS['pageTitle'] = "Supertasks";
}

echo $TEMPLATE->render($OBJECTS);




