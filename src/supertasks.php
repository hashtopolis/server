<?php

use DBA\JoinFilter;
use DBA\Pretask;
use DBA\QueryFilter;
use DBA\OrderFilter;
use DBA\SupertaskPretask;

require_once(dirname(__FILE__) . "/inc/load.php");

/** @var Login $LOGIN */
/** @var array $OBJECTS */

if (!$LOGIN->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}
else if ($LOGIN->getLevel() < DAccessLevel::READ_ONLY) {
  $TEMPLATE = new Template("restricted");
  $OBJECTS['pageTitle'] = "Hashtopussy - Restricted";
  die($TEMPLATE->render($OBJECTS));
}

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

if (isset($_GET['create']) && $_GET['create'] == "new") {
  $MENU->setActive("tasks_supernew");
  $TEMPLATE = new Template("supertasks/create");
  $qF = new QueryFilter(Pretask::IS_MASK_IMPORT, 0, "=");
  $OBJECTS['preTasks'] = $FACTORIES::getPretaskFactory()->filter(array($FACTORIES::FILTER => $qF));
  $OBJECTS['pageTitle'] = "Hashtopussy - Create Supertask";
}
else if (isset($_GET['create']) && $_GET['create'] == "import") {
  $MENU->setActive("tasks_superimport");
  $TEMPLATE = new Template("supertasks/import");
  
  $OBJECTS['crackerBinaryTypes'] = $FACTORIES::getCrackerBinaryTypeFactory()->filter(array());
  $OBJECTS['pageTitle'] = "Hashtopussy - Import Supertask from Masks";
}
else if (isset($_GET['id']) && isset($_GET['new'])) {
  $TEMPLATE = new Template("supertasks/new");
  $supertask = $FACTORIES::getSupertaskFactory()->get($_GET['id']);
  $OBJECTS['orig'] = $supertask->getId();
  $OBJECTS['lists'] = $FACTORIES::getHashlistFactory()->filter(array());
  $OBJECTS['binaries'] = $FACTORIES::getCrackerBinaryTypeFactory()->filter(array());
  $versions = $FACTORIES::getCrackerBinaryFactory()->filter(array());
  usort($versions, array("Util", "versionComparisonBinary"));
  $OBJECTS['versions'] = $versions;
  $OBJECTS['pageTitle'] = "Hashtopussy - Issue Supertask";
}
else if (isset($_GET['id'])) {
  $TEMPLATE = new Template("supertasks/detail");
  $supertask = $FACTORIES::getSupertaskFactory()->get($_GET['id']);
  if ($supertask == null) {
    UI::printError("ERROR", "Invalid supertask ID!");
  }
  $qF = new QueryFilter(SupertaskPretask::SUPERTASK_ID, $supertask->getId(), "=", $FACTORIES::getSupertaskPretaskFactory());
  $jF = new JoinFilter($FACTORIES::getSupertaskPretaskFactory(), SupertaskPretask::PRETASK_ID, Pretask::PRETASK_ID);
  $tasks = $FACTORIES::getPretaskFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::JOIN => $jF));
  $OBJECTS['tasks'] = $tasks[$FACTORIES::getPretaskFactory()->getModelName()];
  $OBJECTS['supertask'] = $supertask;
  $OBJECTS['pageTitle'] = "Hashtopussy - Supertask details for " . $supertask->getSupertaskName();
}
else {
  $supertasks = $FACTORIES::getSupertaskFactory()->filter(array());
  $supertaskTasks = new DataSet();
  foreach ($supertasks as $supertask) {
    $qF = new QueryFilter(SupertaskPretask::SUPERTASK_ID, $supertask->getId(), "=", $FACTORIES::getSupertaskPretaskFactory());
    $jF = new JoinFilter($FACTORIES::getSupertaskPretaskFactory(), SupertaskPretask::PRETASK_ID, Pretask::PRETASK_ID);
    $oF = new OrderFilter(Pretask::PRIORITY, "DESC");
    $joinedTasks = $FACTORIES::getPretaskFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::JOIN => $jF, $FACTORIES::ORDER => $oF));
    $tasks = $joinedTasks[$FACTORIES::getPretaskFactory()->getModelName()];
    $supertaskTasks->addValue($supertask->getId(), $tasks);
  }
  $OBJECTS['tasks'] = $supertaskTasks;
  $OBJECTS['supertasks'] = $supertasks;
  $OBJECTS['pageTitle'] = "Hashtopussy - Supertasks";
}

echo $TEMPLATE->render($OBJECTS);




