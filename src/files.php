<?php

use DBA\File;
use DBA\OrderFilter;
use DBA\QueryFilter;

require_once(dirname(__FILE__) . "/inc/load.php");

/** @var Login $LOGIN */
/** @var array $OBJECTS */

if (!$LOGIN->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']));
  die();
}
else if ($LOGIN->getLevel() < DAccessLevel::USER) {
  $TEMPLATE = new Template("restricted");
  die($TEMPLATE->render($OBJECTS));
}

$TEMPLATE = new Template("files/index");
$MENU->setActive("files");
$message = "";

//catch actions here...
if (isset($_POST['action'])) {
  $fileHandler = new FileHandler();
  $fileHandler->handle($_POST['action']);
}

$view = "dict";
if(isset($_GET['view']) && in_array($_GET['view'], array('dict', 'rule'))){
  $view = $_GET['view'];
}


$qF = new QueryFilter(File::FILE_TYPE, array_search($view, array('dict', 'rule')), "=");
$oF = new OrderFilter(File::FILENAME, "ASC");
$OBJECTS['fileType'] = ($view == "dict")?"Wordlists":"Rules";
$OBJECTS['view'] = $view;
$OBJECTS['files'] = $FACTORIES::getFileFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::ORDER => $oF));;
$OBJECTS['impfiles'] = Util::scanImportDirectory();
$OBJECTS['message'] = $message;

echo $TEMPLATE->render($OBJECTS);




