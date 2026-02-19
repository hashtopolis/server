<?php

require_once(dirname(__FILE__) . "/inc/startup/load.php");

AccessControl::getInstance()->checkPermission(DViewControl::ABOUT_VIEW_PERM);

UI::add('pageTitle', "About");
Template::loadInstance("static/about");
echo Template::getInstance()->render(UI::getObjects());




