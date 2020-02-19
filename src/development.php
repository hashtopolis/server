<?php

require_once(dirname(__FILE__) . "/inc/load.php");

if (!Login::getInstance()->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}

AccessControl::getInstance()->checkPermission(DViewControl::DEVELOPMENT_VIEW_PERM);

Template::loadInstance("development/index");
Menu::get()->setActive("config_dev");

//catch actions here...
if (isset($_POST['action']) && CSRF::check($_POST['csrf'])) {
  $developmentHandler = new DevelopmentHandler();
  $developmentHandler->handle($_POST['action']);
  if (UI::getNumMessages() == 0) {
    Util::refresh();
  }
}

$setups = [];
$cleanups = [];
foreach (HashtopolisSetup::getInstances() as $instance) {
  if ($instance->getSetupType() == DSetupType::REMOVAL) {
    $cleanups[] = $instance;
  }
  else {
    $setups[] = $instance;
  }
}

UI::add('setups', $setups);
UI::add('cleanups', $cleanups);
UI::add('pageTitle', "Development Tools");

echo Template::getInstance()->render(UI::getObjects());




