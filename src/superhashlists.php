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

$TEMPLATE = new Template("superhashlists");
$MENU->setActive("lists_super");
$message = "";

$res = $FACTORIES::getagentsFactory()->getDB()->query("SELECT hashlists.id,hashlists.name,hashlists.secret,hashlists.hashtype,hashlists.format,hashlists.hashcount,hashlists.cracked,GROUP_CONCAT(hashlists2.name ORDER BY hashlists2.id SEPARATOR '<br>') AS lists,hashtypes.description FROM hashlists LEFT JOIN hashtypes ON hashtypes.id=hashlists.hashtype JOIN superhashlists ON superhashlists.id=hashlists.id JOIN hashlists hashlists2 ON hashlists2.id=superhashlists.hashlist WHERE hashlists.format=3 GROUP BY superhashlists.id ORDER BY id ASC");
$res = $res->fetchAll();
$lists = array();
foreach ($res as $list) {
  $set = new DataSet();
  $set->setValues($list);
  $lists[] = $set;
}

$OBJECTS['numSuperhashlists'] = sizeof($lists);
$OBJECTS['lists'] = $lists;
$OBJECTS['message'] = $message;

echo $TEMPLATE->render($OBJECTS);




