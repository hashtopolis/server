<?php

require_once(dirname(__FILE__) . "/inc/load.php");

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
  //TODO: create new superhashlist
}
else{
  $qF = new QueryFilter("format", "3", "=");
  $hashtypes = new DataSet();
  $types = $FACTORIES::getHashTypeFactory()->filter(array());
  foreach($types as $type){
    $hashtypes->addValue($type->getId(), $type->getDescription());
  }
  $OBJECTS['hashtypes'] = $hashtypes;
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

echo $TEMPLATE->render($OBJECTS);




