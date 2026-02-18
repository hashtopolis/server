<?php

// set to 1 for debugging
use Hashtopolis\dba\Factory;
use Hashtopolis\inc\CSRF;
use Hashtopolis\inc\Login;
use Hashtopolis\inc\Menu;
use Hashtopolis\inc\SConfig;
use Hashtopolis\inc\StartupConfig;
use Hashtopolis\inc\UI;
use Hashtopolis\inc\Util;
use Hashtopolis\inc\utils\AccessControl;

ini_set("display_errors", "0");

session_start();

require_once(dirname(__FILE__) . "/include.php");

UI::add('version', StartupConfig::getInstance()->getVersion());
UI::add('host', StartupConfig::getInstance()->getHost());
UI::add('gitcommit', Util::getGitCommit());
UI::add('build', '');

// Darkmode
if (isset($_COOKIE['toggledarkmode']) && $_COOKIE['toggledarkmode'] == '1') {
  UI::add('toggledarkmode', 1);
}
else {
  UI::add('toggledarkmode', 0);
}

if (strlen(Util::getGitCommit()) == 0) {
  $storedBuild = Factory::getStoredValueFactory()->get("build");
  if ($storedBuild != null) {
    UI::add('build', $storedBuild->getVal());
  }
}

UI::add('menu', Menu::get());
UI::add('messages', []);

UI::add('pageTitle', "");
UI::add('login', Login::getInstance());
if (Login::getInstance()->isLoggedin()) {
  UI::add('user', Login::getInstance()->getUser());
  AccessControl::getInstance(Login::getInstance()->getUser());
}

UI::add('config', SConfig::getInstance());

//set autorefresh to false for all pages
UI::add('autorefresh', -1);

UI::add('accessControl', AccessControl::getInstance());

// CSRF setup
CSRF::init();
