<?php

use DBA\CrackerBinary;
use DBA\CrackerBinaryType;
use DBA\OrderFilter;
use DBA\QueryFilter;
use DBA\Factory;

require_once(dirname(__FILE__) . "/inc/load.php");

if (!Login::getInstance()->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}

AccessControl::getInstance()->checkPermission(DViewControl::PREPROCESSORS_VIEW_PERM);

Template::loadInstance("preprocessors/index");
Menu::get()->setActive("config_preprocessors");

//catch actions here...
if (isset($_POST['action']) && CSRF::check($_POST['csrf'])) {
  $preprocessorHandler = new PreprocessorHandler();
  $preprocessorHandler->handle($_POST['action']);
  if (UI::getNumMessages() == 0) {
    Util::refresh();
  }
}

if (isset($_GET['new']) && AccessControl::getInstance()->hasPermission(DAccessControl::PREPROCESSORS_ACCESS)) {
  Template::loadInstance("preprocessors/new");
  UI::add('pageTitle', "Add Preprocessor");
}
else if (isset($_GET['edit']) && AccessControl::getInstance()->hasPermission(DAccessControl::PREPROCESSORS_ACCESS)) {
  $binary = Factory::getCrackerBinaryFactory()->get($_GET['id']);
  if ($binary !== null) {
    UI::add('binary', $binary);
    Template::loadInstance("preprocessors/edit");
    UI::add('binaryType', Factory::getCrackerBinaryTypeFactory()->get($binary->getCrackerBinaryTypeId()));
    UI::add('pageTitle', "Edit Cracker Binary Version for " . UI::get('binaryType')->getTypeName());
  }
}
else if (isset($_GET['id'])) {
  $binaryType = Factory::getCrackerBinaryTypeFactory()->get($_GET['id']);
  if ($binaryType !== null) {
    UI::add('binaryType', $binaryType);
    Template::loadInstance("preprocessors/details");
    $qF = new QueryFilter(CrackerBinary::CRACKER_BINARY_TYPE_ID, $binaryType->getId(), "=");
    UI::add('binaries', Factory::getCrackerBinaryFactory()->filter([Factory::FILTER => $qF]));
    UI::add('pageTitle', "Cracker Binary details for " . $binaryType->getTypeName());
  }
}
else {
  $oF = new OrderFilter(CrackerBinaryType::TYPE_NAME, "ASC");
  UI::add('binaryTypes', Factory::getCrackerBinaryTypeFactory()->filter([Factory::ORDER => $oF]));
  $binariesVersions = new DataSet();
  foreach (UI::get('binaryTypes') as $binaryType) { /** @var CrackerBinaryType $binaryType */
    $qF = new QueryFilter(CrackerBinary::CRACKER_BINARY_TYPE_ID, $binaryType->getId(), "=");
    $binaries = Factory::getCrackerBinaryFactory()->filter([Factory::FILTER => $qF]);
    $arr = array();
    usort($binaries, ["Util", "versionComparisonBinary"]);
    foreach ($binaries as $binary) {
      if (!isset($arr[$binary->getVersion()])) {
        $arr[$binary->getVersion()] = $binary->getVersion();
      }
    }
    $binariesVersions->addValue($binaryType->getId(), implode("<br>", $arr));
  }
  UI::add('versions', $binariesVersions);
  UI::add('pageTitle', "Preprocessors");
}

echo Template::getInstance()->render(UI::getObjects());




