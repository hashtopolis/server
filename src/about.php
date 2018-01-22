<?php

require_once(dirname(__FILE__) . "/inc/load.php");

$TEMPLATE = new Template("static/about");
$OBJECTS['pageTitle'] = "About";

echo $TEMPLATE->render($OBJECTS);




