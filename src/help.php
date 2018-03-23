<?php

require_once(dirname(__FILE__) . "/inc/load.php");

$ACCESS_CONTROL->checkViewPermission(DViewControl::HELP_VIEW_PERM);

$TEMPLATE = new Template("static/help");
$OBJECTS['pageTitle'] = "Help";

echo $TEMPLATE->render($OBJECTS);




