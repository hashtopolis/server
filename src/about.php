<?php

require_once(dirname(__FILE__) . "/inc/load.php");

AccessControl::getInstance()->checkPermission(DViewControl::ABOUT_VIEW_PERM);

$TEMPLATE = new Template("static/about");
UI::add('pageTitle', "About");

echo $TEMPLATE->render(UI::getObjects());




