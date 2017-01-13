<?php

use DBA\JoinFilter;
use DBA\QueryFilter;

require_once(dirname(__FILE__) . "/inc/load.php");

/** @var Login $LOGIN */
/** @var array $OBJECTS */

if (!$LOGIN->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF']));
  die();
}
else if ($LOGIN->getLevel() < 5) {
  $TEMPLATE = new Template("restricted");
  die($TEMPLATE->render($OBJECTS));
}

$TEMPLATE = new Template("superhashlists/index");
$MENU->setActive("lists_super");

if(isset($_GET['new'])){
  $TEMPLATE = new Template("superhashlists/new");
  $MENU->setActive("lists_snew");
  $qF = new QueryFilter("format", 3, "<>");
  $OBJECTS['lists'] = $FACTORIES::getHashlistFactory()->filter(array('filter' => $qF));
}
else{
  $qF = new QueryFilter("format", "3", "=");
  $lists = $FACTORIES::getHashlistFactory()->filter(array('filter' => $qF));
  $OBJECTS['lists'] = $lists;
  $subLists = new DataSet();
  foreach($lists as $list){
    $qF = new QueryFilter("superHashlistId", $list->getId(), "=", $FACTORIES::getSuperHashlistHashlistFactory());
    $jF = new JoinFilter($FACTORIES::getSuperHashlistHashlistFactory(), "hashlistId", "hashlistId");
    $ll = $FACTORIES::getHashlistFactory()->filter(array('filter' => $qF, 'join' => $jF));
    $subLists->addValue($list->getId(), $ll['Hashlist']);
  }
  $OBJECTS['subLists'] = $subLists;
}

$hashtypes = new DataSet();
$types = $FACTORIES::getHashTypeFactory()->filter(array());
foreach($types as $type){
  $hashtypes->addValue($type->getId(), $type->getDescription());
}
$OBJECTS['hashtypes'] = $hashtypes;

echo $TEMPLATE->render($OBJECTS);




