<?php

require_once(dirname(__FILE__) . "/inc/load.php");

/** @var Login $LOGIN */
/** @var array $OBJECTS */

if (!$LOGIN->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}

$ACCESS_CONTROL->checkViewPermission(DViewControl::SEARCH_VIEW_PERM);

$TEMPLATE = new Template("search");
$OBJECTS['pageTitle'] = "Search Hashes";
$MENU->setActive("lists_search");

$OBJECTS['result'] = false;

//catch actions here...
if (isset($_POST['action']) && CSRF::check($_POST['csrf'])) {
  $searchHandler = new SearchHandler();
  $searchHandler->handle($_POST['action']);
  if (UI::getNumMessages() == 0) {
    Util::refresh();
  }
}

echo $TEMPLATE->render($OBJECTS);




