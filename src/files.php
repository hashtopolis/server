<?php

use DBA\File;
use DBA\OrderFilter;
use DBA\QueryFilter;

require_once(dirname(__FILE__) . "/inc/load.php");

/** @var Login $LOGIN */
/** @var array $OBJECTS */

if (!$LOGIN->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}

$ACCESS_CONTROL->checkPermission(DViewControl::FILES_VIEW_PERM);

$TEMPLATE = new Template("files/index");
$MENU->setActive("files");
$message = "";

//catch actions here...
if (isset($_POST['action']) && CSRF::check($_POST['csrf'])) {
  $fileHandler = new FileHandler();
  $fileHandler->handle($_POST['action']);
  if (UI::getNumMessages() == 0) {
    Util::refresh();
  }
}

$view = "dict";
if (isset($_GET['view']) && in_array($_GET['view'], array('dict', 'rule'))) {
  $view = $_GET['view'];
}

if (isset($_GET['edit'])) {
  $file = $FACTORIES::getFileFactory()->get($_GET['edit']);
  if ($file == null) {
    UI::addMessage(UI::ERROR, "Invalid file ID!");
  }
  else {
    $OBJECTS['file'] = $file;
    $TEMPLATE = new Template("files/edit");
    $OBJECTS['pageTitle'] = "Edit File " . $file->getFilename();
  }
}
else {
  $qF = new QueryFilter(File::FILE_TYPE, array_search($view, array('dict', 'rule')), "=");
  $oF = new OrderFilter(File::FILENAME, "ASC");
  $OBJECTS['fileType'] = ($view == "dict") ? "Wordlists" : "Rules";
  $OBJECTS['files'] = $FACTORIES::getFileFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::ORDER => $oF));;
  $OBJECTS['impfiles'] = Util::scanImportDirectory();
  $OBJECTS['pageTitle'] = "Files";
}
$OBJECTS['view'] = $view;

echo $TEMPLATE->render($OBJECTS);




