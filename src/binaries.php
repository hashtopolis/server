<?php

use DBA\Factory;

require_once(dirname(__FILE__) . "/inc/startup/load.php");

if (!Login::getInstance()->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}

AccessControl::getInstance()->checkPermission(DViewControl::BINARIES_VIEW_PERM);

UI::add('newBinary', false);
UI::add('editBinary', false);

Template::loadInstance("binaries");
Menu::get()->setActive("config_binaries");

//catch actions here...
if (isset($_POST['action']) && CSRF::check($_POST['csrf'])) {
  $binaryHandler = new AgentBinaryHandler();
  $binaryHandler->handle($_POST['action']);
  if (UI::getNumMessages() == 0) {
    Util::refresh();
  }
}
UI::add('pageTitle', "Agent Binaries");

if (isset($_GET['new'])) {
  UI::add('newBinary', true);
  UI::add('pageTitle', "New Agent Binary");
}
else if (isset($_GET['edit'])) {
  $bin = Factory::getAgentBinaryFactory()->get($_GET['edit']);
  if ($bin == null) {
    UI::printError("ERROR", "Invalid agent binary ID!");
  }
  UI::add('pageTitle', "Edit Agent Binary of type " . $bin->getBinaryType());
  UI::add('editBinary', true);
  UI::add('bin', $bin);
}
UI::add('binaries', Factory::getAgentBinaryFactory()->filter([]));

echo Template::getInstance()->render(UI::getObjects());




