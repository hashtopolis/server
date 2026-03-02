<?php

use Hashtopolis\inc\defines\DViewControl;
use Hashtopolis\inc\templating\Template;
use Hashtopolis\inc\UI;
use Hashtopolis\inc\utils\AccessControl;

require_once(dirname(__FILE__) . "/inc/startup/load.php");

AccessControl::getInstance()->checkPermission(DViewControl::ABOUT_VIEW_PERM);

UI::add('pageTitle', "About");
Template::loadInstance("static/about");
echo Template::getInstance()->render(UI::getObjects());




