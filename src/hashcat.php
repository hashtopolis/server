<?php

require_once(dirname(__FILE__) . "/inc/load.php");

if (!$LOGIN->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF']));
  die();
}
else if ($LOGIN->getLevel() < 20) {
  $TEMPLATE = new Template("restricted");
  die($TEMPLATE->render($OBJECTS));
}

$TEMPLATE = new Template("hashcat/index");
$MENU->setActive("hashcat_list");

//catch actions here...
if (isset($_POST['action'])) {
  $hashcatHandler = new HashcatHandler();
  $hashcatHandler->handle($_POST['action']);
}

if(isset($_GET['new'])){
  $TEMPLATE = new Template("hashcat/new");
  $MENU->setActive("hashcat_new");
  $oF = new OrderFilter("time", "DESC LIMIT 1");
  $releases = $FACTORIES::getHashcatReleaseFactory()->filter(array('order' => $oF));
  $rootDir = "";
  $common = "";
  if(sizeof($releases) > 0){
    $rootDir = $releases[0]->getRootdir();
    $common = $releases[0]->getCommonFiles();
  }
  $OBJECTS['rootDir'] = $rootDir;
  $OBJECTS['common'] = $common;
}
else{
  $oF = new OrderFilter("time", "DESC");
  $OBJECTS['releases'] = $FACTORIES::getHashcatReleaseFactory()->filter(array('order' => $oF));
}

echo $TEMPLATE->render($OBJECTS);




