<?php

use DBA\CrackerBinary;
use DBA\CrackerBinaryType;
use DBA\OrderFilter;
use DBA\QueryFilter;

require_once(dirname(__FILE__) . "/inc/load.php");

/** @var Login $LOGIN */
/** @var array $OBJECTS */

if (!$LOGIN->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}

$ACCESS_CONTROL->checkPermission(DViewControl::CRACKERS_VIEW_PERM);

$TEMPLATE = new Template("crackers/index");
$MENU->setActive("crackers_list");

//catch actions here...
if (isset($_POST['action']) && CSRF::check($_POST['csrf'])) {
  $crackerHandler = new CrackerHandler();
  $crackerHandler->handle($_POST['action']);
  if (UI::getNumMessages() == 0) {
    Util::refresh();
  }
}

if (isset($_GET['new']) && isset($_GET['id']) && $ACCESS_CONTROL->hasPermission(DAccessControl::CRACKER_BINARY_ACCESS)) {
  $binaryType = $FACTORIES::getCrackerBinaryTypeFactory()->get($_GET['id']);
  if ($binaryType !== null) {
    $OBJECTS['binaryType'] = $binaryType;
    $TEMPLATE = new Template("crackers/newVersion");
    $OBJECTS['pageTitle'] = "Add new Cracker Binary Version";
  }
}
else if (isset($_GET['new']) && $ACCESS_CONTROL->hasPermission(DAccessControl::CRACKER_BINARY_ACCESS)) {
  $TEMPLATE = new Template("crackers/new");
  $MENU->setActive("crackers_new");
  $OBJECTS['pageTitle'] = "Add Cracker Binary";
}
else if (isset($_GET['edit']) && $ACCESS_CONTROL->hasPermission(DAccessControl::CRACKER_BINARY_ACCESS)) {
  $binary = $FACTORIES::getCrackerBinaryFactory()->get($_GET['id']);
  if ($binary !== null) {
    $OBJECTS['binary'] = $binary;
    $TEMPLATE = new Template("crackers/editVersion");
    $MENU->setActive("crackers_edit");
    $OBJECTS['binaryType'] = $FACTORIES::getCrackerBinaryTypeFactory()->get($binary->getCrackerBinaryTypeId());
    $OBJECTS['pageTitle'] = "Edit Cracker Binary Version for " . $OBJECTS['binaryType']->getTypeName();
  }
}
else if (isset($_GET['id'])) {
  $binaryType = $FACTORIES::getCrackerBinaryTypeFactory()->get($_GET['id']);
  if ($binaryType !== null) {
    $OBJECTS['binaryType'] = $binaryType;
    $TEMPLATE = new Template("crackers/detail");
    $qF = new QueryFilter(CrackerBinary::CRACKER_BINARY_TYPE_ID, $binaryType->getId(), "=");
    $OBJECTS['binaries'] = $FACTORIES::getCrackerBinaryFactory()->filter(array($FACTORIES::FILTER => $qF));
    $OBJECTS['pageTitle'] = "Cracker Binary details for " . $binaryType->getTypeName();
  }
}
else {
  $oF = new OrderFilter(CrackerBinaryType::TYPE_NAME, "ASC");
  $OBJECTS['binaryTypes'] = $FACTORIES::getCrackerBinaryTypeFactory()->filter(array($FACTORIES::ORDER => $oF));
  $binariesVersions = new DataSet();
  foreach ($OBJECTS['binaryTypes'] as $binaryType) {
    $qF = new QueryFilter(CrackerBinary::CRACKER_BINARY_TYPE_ID, $binaryType->getId(), "=");
    $binaries = $FACTORIES::getCrackerBinaryFactory()->filter(array($FACTORIES::FILTER => $qF));
    $arr = array();
    usort($binaries, array("Util", "versionComparisonBinary"));
    foreach ($binaries as $binary) {
      if (!isset($arr[$binary->getVersion()])) {
        $arr[$binary->getVersion()] = $binary->getVersion();
      }
    }
    $binariesVersions->addValue($binaryType->getId(), implode("<br>", $arr));
  }
  $OBJECTS['versions'] = $binariesVersions;
  $OBJECTS['pageTitle'] = "Cracker Binaries";
}

echo $TEMPLATE->render($OBJECTS);




