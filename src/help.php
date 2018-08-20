<?php

require_once(dirname(__FILE__) . "/inc/load.php");

AccessControl::getInstance()->checkPermission(DViewControl::HELP_VIEW_PERM);

$TEMPLATE = new Template("static/help");
UI::add('pageTitle', "Help");

echo $TEMPLATE->render(UI::getObjects());




