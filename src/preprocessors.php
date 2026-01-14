<?php

use DBA\OrderFilter;
use DBA\Preprocessor;
use DBA\Factory;

require_once(dirname(__FILE__) . "/inc/startup/load.php");

if (!Login::getInstance()->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}

AccessControl::getInstance()->checkPermission(DViewControl::PREPROCESSORS_VIEW_PERM);

Template::loadInstance("preprocessors/index");
Menu::get()->setActive("config_preprocessors");

//catch actions here...
if (isset($_POST['action']) && CSRF::check($_POST['csrf'])) {
  $preprocessorHandler = new PreprocessorHandler();
  $preprocessorHandler->handle($_POST['action']);
  if (UI::getNumMessages() == 0) {
    Util::refresh();
  }
}

if (isset($_GET['new']) && AccessControl::getInstance()->hasPermission(DAccessControl::SERVER_CONFIG_ACCESS)) {
  Template::loadInstance("preprocessors/new");
  UI::add('pageTitle', "Add Preprocessor");
}
else if (isset($_GET['edit']) && AccessControl::getInstance()->hasPermission(DAccessControl::SERVER_CONFIG_ACCESS)) {
  $preprocessor = Factory::getPreprocessorFactory()->get($_GET['id']);
  if ($preprocessor !== null) {
    UI::add('preprocessor', $preprocessor);
    Template::loadInstance("preprocessors/edit");
    UI::add('pageTitle', "Edit preprocessor " . htmlentities($preprocessor->getName(), ENT_QUOTES, "UTF-8"));
  }
}
else if (isset($_GET['id'])) {
  $preprocessor = Factory::getPreprocessorFactory()->get($_GET['id']);
  if ($preprocessor !== null) {
    UI::add('preprocessor', $preprocessor);
    Template::loadInstance("preprocessors/details");
    UI::add('pageTitle', "Preprocessor details for " . htmlentities($preprocessor->getName(), ENT_QUOTES, "UTF-8"));
  }
}
else {
  $oF = new OrderFilter(Preprocessor::NAME, "ASC");
  UI::add('preprocessors', Factory::getPreprocessorFactory()->filter([Factory::ORDER => $oF]));
  UI::add('pageTitle', "Preprocessors");
}

echo Template::getInstance()->render(UI::getObjects());




