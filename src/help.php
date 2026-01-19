<?php

require_once(dirname(__FILE__) . "/inc/startup/load.php");

AccessControl::getInstance()->checkPermission(DViewControl::HELP_VIEW_PERM);

Template::loadInstance("static/help");
UI::add('pageTitle', "Help");
echo Template::getInstance()->render(UI::getObjects());




