<?php

use DBA\Hashlist;
use DBA\HashlistHashlist;
use DBA\JoinFilter;
use DBA\QueryFilter;
use DBA\Factory;

require_once(dirname(__FILE__) . "/inc/startup/load.php");

if (!Login::getInstance()->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}

AccessControl::getInstance()->checkPermission(DViewControl::SUPERHASHLISTS_VIEW_PERM);

Template::loadInstance("superhashlists/index");
Menu::get()->setActive("lists_super");

if (isset($_GET['new']) && AccessControl::getInstance()->hasPermission(DAccessControl::CREATE_SUPERHASHLIST_ACCESS)) {
  Template::loadInstance("superhashlists/new");
  Menu::get()->setActive("lists_snew");
  UI::add('lists', HashlistUtils::getHashlists(Login::getInstance()->getUser()));
  UI::add('pageTitle', "Create Superhashlist");
}
else {
  $qF = new QueryFilter(Hashlist::FORMAT, DHashlistFormat::SUPERHASHLIST, "=");
  $lists = Factory::getHashlistFactory()->filter([Factory::FILTER => $qF]);
  UI::add('lists', $lists);
  $subLists = new DataSet();
  foreach ($lists as $list) {
    $qF = new QueryFilter(HashlistHashlist::PARENT_HASHLIST_ID, $list->getId(), "=", Factory::getHashlistHashlistFactory());
    $jF = new JoinFilter(Factory::getHashlistHashlistFactory(), HashlistHashlist::HASHLIST_ID, Hashlist::HASHLIST_ID);
    $ll = Factory::getHashlistFactory()->filter([Factory::FILTER => $qF, Factory::JOIN => $jF]);
    $subLists->addValue($list->getId(), $ll[Factory::getHashlistFactory()->getModelName()]);
  }
  UI::add('subLists', $subLists);
  UI::add('pageTitle', "Superhashlists");
}

$hashtypes = new DataSet();
$types = Factory::getHashTypeFactory()->filter([]);
foreach ($types as $type) {
  $hashtypes->addValue($type->getId(), $type->getDescription());
}
UI::add('hashtypes', $hashtypes);

echo Template::getInstance()->render(UI::getObjects());




