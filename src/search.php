<?php

require_once(dirname(__FILE__) . "/inc/startup/load.php");

if (!Login::getInstance()->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}

AccessControl::getInstance()->checkPermission(DViewControl::SEARCH_VIEW_PERM);

Template::loadInstance("search");
UI::add('pageTitle', "Search Hashes");
Menu::get()->setActive("lists_search");

UI::add('result', false);

//catch actions here...
if (isset($_POST['action']) && CSRF::check($_POST['csrf'])) {
  $searchHandler = new SearchHandler();
  $searchHandler->handle($_POST['action']);
  if (UI::getNumMessages() == 0) {
    Util::refresh();
  }
}

echo Template::getInstance()->render(UI::getObjects());




