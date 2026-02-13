<?php

use Hashtopolis\inc\defines\DViewControl;
use Hashtopolis\inc\templating\Template;
use Hashtopolis\inc\UI;
use Hashtopolis\inc\utils\AccessControl;

require_once(dirname(__FILE__) . "/inc/startup/load.php");

AccessControl::getInstance()->checkPermission(DViewControl::HELP_VIEW_PERM);

Template::loadInstance("static/help");
UI::add('pageTitle', "Help");
echo Template::getInstance()->render(UI::getObjects());




