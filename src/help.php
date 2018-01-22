<?php

require_once(dirname(__FILE__) . "/inc/load.php");

$TEMPLATE = new Template("static/help");
$OBJECTS['pageTitle'] = "Help";

echo $TEMPLATE->render($OBJECTS);




