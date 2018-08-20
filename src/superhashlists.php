<?php

use DBA\Hashlist;
use DBA\HashlistHashlist;
use DBA\JoinFilter;
use DBA\QueryFilter;
use DBA\Factory;

require_once(dirname(__FILE__) . "/inc/load.php");

/** @var Login $LOGIN */
/** @var array $OBJECTS */

if (!$LOGIN->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}

$ACCESS_CONTROL->checkPermission(DViewControl::SUPERHASHLISTS_VIEW_PERM);

$TEMPLATE = new Template("superhashlists/index");
$MENU->setActive("lists_super");

if (isset($_GET['new']) && $ACCESS_CONTROL->hasPermission(DAccessControl::CREATE_SUPERHASHLIST_ACCESS)) {
  $TEMPLATE = new Template("superhashlists/new");
  $MENU->setActive("lists_snew");
  $qF = new QueryFilter(Hashlist::FORMAT, DHashlistFormat::SUPERHASHLIST, "<>");
  $OBJECTS['lists'] = Factory::getHashlistFactory()->filter([Factory::FILTER => $qF]);
  $OBJECTS['pageTitle'] = "Create Superhashlist";
}
else {
  $qF = new QueryFilter(Hashlist::FORMAT, DHashlistFormat::SUPERHASHLIST, "=");
  $lists = Factory::getHashlistFactory()->filter([Factory::FILTER => $qF]);
  $OBJECTS['lists'] = $lists;
  $subLists = new DataSet();
  foreach ($lists as $list) {
    $qF = new QueryFilter(HashlistHashlist::PARENT_HASHLIST_ID, $list->getId(), "=", Factory::getHashlistHashlistFactory());
    $jF = new JoinFilter(Factory::getHashlistHashlistFactory(), HashlistHashlist::HASHLIST_ID, Hashlist::HASHLIST_ID);
    $ll = Factory::getHashlistFactory()->filter([Factory::FILTER => $qF, Factory::JOIN => $jF]);
    $subLists->addValue($list->getId(), $ll[Factory::getHashlistFactory()->getModelName()]);
  }
  $OBJECTS['subLists'] = $subLists;
  $OBJECTS['pageTitle'] = "Superhashlists";
}

$hashtypes = new DataSet();
$types = Factory::getHashTypeFactory()->filter([]);
foreach ($types as $type) {
  $hashtypes->addValue($type->getId(), $type->getDescription());
}
$OBJECTS['hashtypes'] = $hashtypes;

echo $TEMPLATE->render($OBJECTS);




