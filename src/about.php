<?php

require_once(dirname(__FILE__) . "/inc/load.php");

AccessControl::getInstance()->checkPermission(DViewControl::ABOUT_VIEW_PERM);

$TEMPLATE = new Template("static/about");
$OBJECTS['pageTitle'] = "About";

echo $TEMPLATE->render($OBJECTS);




